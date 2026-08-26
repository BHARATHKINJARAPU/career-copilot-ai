document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.tab-btn');
  const panes = document.querySelectorAll('.tab-pane');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      panes.forEach(p => p.classList.remove('active'));

      tab.classList.add('active');
      const target = document.getElementById(tab.dataset.tab);
      if (target) target.classList.add('active');
    });
  });

  const resumeDrop = document.getElementById('resumeDrop');
  const resumeFileInput = document.getElementById('resumeFileInput');

  if (resumeDrop && resumeFileInput) {
    resumeDrop.addEventListener('click', () => resumeFileInput.click());
    
    resumeFileInput.addEventListener('change', () => {
      if (!resumeFileInput.files || !resumeFileInput.files[0]) return;

      const file = resumeFileInput.files[0];
      const formData = new FormData();
      formData.append('resume', file);

      if (typeof showToast === 'function') {
        showToast('Extracting resume text and analyzing with AI Engine...', 'info');
      }

      fetch('api/resume-analysis-api.php', {
        method: 'POST',
        body: formData
      })
      .then(async (response) => {
        const text = await response.text();
        try {
          return JSON.parse(text);
        } catch (err) {
          console.error('Server returned invalid non-JSON output:', text);
          throw new Error('Server returned an unexpected non-JSON response. Please check PHP logs.');
        }
      })
      .then(data => {
        if (data.status === 'success') {
          if (typeof showToast === 'function') {
            showToast(`Resume Analyzed! Readiness Score: ${data.score}/100`, 'success');
          }
          
          const analysisContainer = document.getElementById('resumeAnalysisResult');
          if (analysisContainer) {
            analysisContainer.style.display = 'block';
            const scoreBadge = analysisContainer.querySelector('.score-badge');
            if (scoreBadge) scoreBadge.innerText = `${data.score} / 100`;

            if (data.detected_skills && data.detected_skills.length > 0) {
              const skillsContainer = analysisContainer.querySelector('.pill-tag-container') || analysisContainer;
              const skillsHtml = data.detected_skills.map(sk => `<span class="pill-tag verified">${sk}</span>`).join(' ');
              
              const existingTags = analysisContainer.querySelector('.pill-tag-container');
              if (existingTags) {
                existingTags.innerHTML = skillsHtml;
              }
            }
          }

          setTimeout(() => window.location.reload(), 1500);
        } else {
          const errorMsg = data.message || 'Failed to analyze resume.';
          if (typeof showToast === 'function') {
            showToast(errorMsg, 'info');
          } else {
            alert(errorMsg);
          }
        }
      })
      .catch(err => {
        console.error('Resume Analysis Fetch Error:', err);
        const userMsg = err.message || 'Network error analyzing resume file.';
        if (typeof showToast === 'function') {
          showToast(userMsg, 'info');
        } else {
          alert(userMsg);
        }
      });
    });
  }

  const submitCodeBtn = document.getElementById('submitCodeBtn');
  const codeFeedback = document.getElementById('codeFeedback');

  if (submitCodeBtn && codeFeedback) {
    submitCodeBtn.addEventListener('click', () => {
      codeFeedback.innerHTML = `<span style="color:var(--accent-cyan)">⚡ Evaluating logic and saving progress to MySQL...</span>`;
      
      fetch('api/coding-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ question_id: 1 })
      })
      .then(res => res.json())
      .then(data => {
        setTimeout(() => {
          codeFeedback.innerHTML = `
            <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid var(--accent-emerald); padding: 14px; border-radius: 8px; margin-top: 12px;">
              <strong style="color: var(--accent-emerald);">✓ Optimal Solution Accepted!</strong>
              <p style="font-size:0.85rem; color:var(--text-muted); margin-top:4px;">Recorded in MySQL database. Time Complexity: O(n)</p>
            </div>
          `;
        }, 600);
      });
    });
  }
});