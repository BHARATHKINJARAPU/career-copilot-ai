document.addEventListener('DOMContentLoaded', () => {
  const startBtn = document.getElementById('startInterviewBtn');
  const setupSection = document.getElementById('interviewSetup');
  const sessionSection = document.getElementById('interviewSession');
  const reportSection = document.getElementById('interviewReport');

  const roleSelect = document.getElementById('roleSelect');
  const trackSelect = document.getElementById('trackSelect');
  const difficultySelect = document.getElementById('difficultySelect');

  const qNumber = document.getElementById('qNumber');
  const qText = document.getElementById('qText');
  const roleBadge = document.getElementById('roleBadge');
  const answerText = document.getElementById('answerText');

  const answerInputGroup = document.getElementById('answerInputGroup');
  const submitAnswerBtn = document.getElementById('submitAnswerBtn');
  const singleEvaluationCard = document.getElementById('singleEvaluationCard');
  const nextQuestionBtn = document.getElementById('nextQuestionBtn');
  const progressFill = document.getElementById('interviewProgressFill');

  let activeQuestions = [];
  let currentIdx = 0;
  let evaluatedResults = [];

  if (startBtn) {
    startBtn.addEventListener('click', () => {
      const selectedRole = roleSelect ? roleSelect.value : 'Full Stack Developer';
      if (roleBadge) roleBadge.innerText = selectedRole;

      // Fetch questions from API
      fetch('api/interview-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'get_questions',
          role: selectedRole
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success' && data.questions && data.questions.length > 0) {
          activeQuestions = data.questions;
          currentIdx = 0;
          evaluatedResults = [];

          setupSection.style.display = 'none';
          sessionSection.style.display = 'block';
          reportSection.style.display = 'none';

          loadCurrentQuestion();
        } else {
          showToast('Failed to load interview questions. Please try again.', 'info');
        }
      })
      .catch(err => {
        console.error('Interview fetch error:', err);
        showToast('Network error initializing interview.', 'info');
      });
    });
  }

  function loadCurrentQuestion() {
    if (currentIdx >= activeQuestions.length) return;

    const q = activeQuestions[currentIdx];
    qNumber.innerText = `Question ${currentIdx + 1} of ${activeQuestions.length}`;
    qText.innerText = q.question;
    answerText.value = '';

    answerInputGroup.style.display = 'block';
    singleEvaluationCard.style.display = 'none';
    progressFill.style.width = `${((currentIdx + 1) / activeQuestions.length) * 100}%`;
  }

  if (submitAnswerBtn) {
    submitAnswerBtn.addEventListener('click', () => {
      const text = answerText.value.trim();
      if (!text) {
        showToast('Please provide an answer before submitting.', 'info');
        answerText.focus();
        return;
      }

      submitAnswerBtn.disabled = true;
      submitAnswerBtn.innerText = 'Evaluating logic with AI...';

      const q = activeQuestions[currentIdx];

      fetch('api/interview-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'evaluate_question',
          role: roleSelect.value,
          question_id: q.id,
          student_answer: text,
          difficulty: difficultySelect.value
        })
      })
      .then(res => res.json())
      .then(data => {
        submitAnswerBtn.disabled = false;
        submitAnswerBtn.innerText = 'Submit Answer & Evaluate ⚡';

        if (data.status === 'success') {
          evaluatedResults.push(data);
          displaySingleEvaluation(data);
        } else {
          showToast(data.message || 'Error evaluating answer.', 'info');
        }
      })
      .catch(err => {
        submitAnswerBtn.disabled = false;
        submitAnswerBtn.innerText = 'Submit Answer & Evaluate ⚡';
        showToast('Network error evaluating response.', 'info');
      });
    });
  }

  function displaySingleEvaluation(evalData) {
    answerInputGroup.style.display = 'none';
    singleEvaluationCard.style.display = 'block';

    document.getElementById('qScoreBadge').innerText = `${evalData.question_score} / ${evalData.max_score}`;
    document.getElementById('evalSummaryText').innerText = evalData.evaluation;
    document.getElementById('referenceAnswerText').innerText = evalData.correct_answer;

    const strList = document.getElementById('strengthsList');
    strList.innerHTML = (evalData.strengths || []).map(s => `<li>${s}</li>`).join('');

    const mistList = document.getElementById('mistakesList');
    const combinedMistakes = [...(evalData.mistakes || []), ...(evalData.missing_points || [])];
    mistList.innerHTML = combinedMistakes.map(m => `<li>${m}</li>`).join('');

    if (currentIdx + 1 >= activeQuestions.length) {
      nextQuestionBtn.innerText = 'Finish Interview & View Report 🚀';
    } else {
      nextQuestionBtn.innerText = 'Next Question →';
    }
  }

  if (nextQuestionBtn) {
    nextQuestionBtn.addEventListener('click', () => {
      currentIdx++;
      if (currentIdx < activeQuestions.length) {
        loadCurrentQuestion();
      } else {
        // Complete session and calculate final score
        fetch('api/interview-api.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'finish_interview',
            role: roleSelect.value,
            track: trackSelect.value,
            difficulty: difficultySelect.value,
            evaluations: evaluatedResults
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            sessionSection.style.display = 'none';
            reportSection.style.display = 'block';

            document.getElementById('finalScoreBadge').innerText = `${data.final_score}%`;
            document.getElementById('performanceGradeText').innerText = `${data.grade} (${data.total_score} / ${data.total_possible} Points)`;

            const repStr = document.getElementById('reportStrengths');
            repStr.innerHTML = (data.strengths || ['Solid technical effort.']).map(s => `<li>${s}</li>`).join('');

            const repWeak = document.getElementById('reportWeaknesses');
            repWeak.innerHTML = (data.weaknesses || ['Continue practicing system architectural concepts.']).map(w => `<li>${w}</li>`).join('');

            if (typeof showToast === 'function') {
              showToast(`Interview completed! Calculated Score: ${data.final_score}%`, 'success');
            }
          }
        });
      }
    });
  }
});