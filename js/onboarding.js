document.addEventListener('DOMContentLoaded', () => {
  let currentStep = 1;
  const totalSteps = 5;

  const onboardingForm = document.getElementById('onboardingForm');
  const stepTitleText = document.getElementById('stepTitleText');
  const progressFill = document.getElementById('onboardingProgressFill');
  const stepIndicators = document.querySelectorAll('.step-indicator');
  const stepPanes = document.querySelectorAll('.onboarding-step-pane');

  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const submitBtn = document.getElementById('submitOnboardingBtn');

  const titles = [
    "Step 1 of 5: Personal & Campus Details",
    "Step 2 of 5: B.Tech Academic Profile",
    "Step 3 of 5: Target Career Role & Goals",
    "Step 4 of 5: Technical Skills (Starts at Zero)",
    "Step 5 of 5: Strengths, Challenges & Situation Notes"
  ];

  function updateStepView(step) {
    currentStep = step;
    if (stepTitleText) stepTitleText.innerText = titles[step - 1];
    if (progressFill) progressFill.style.width = `${(step / totalSteps) * 100}%`;

    stepIndicators.forEach(ind => {
      const s = parseInt(ind.dataset.step);
      if (s <= step) {
        ind.classList.add('active');
      } else {
        ind.classList.remove('active');
      }
    });

    stepPanes.forEach(pane => {
      if (parseInt(pane.dataset.pane) === step) {
        pane.classList.add('active');
      } else {
        pane.classList.remove('active');
      }
    });

    if (prevBtn) prevBtn.style.display = step > 1 ? 'inline-flex' : 'none';
    if (nextBtn) nextBtn.style.display = step < totalSteps ? 'inline-flex' : 'none';
    if (submitBtn) submitBtn.style.display = step === totalSteps ? 'inline-flex' : 'none';

    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function validateStep(step) {
    const currentPane = document.querySelector(`.onboarding-step-pane[data-pane="${step}"]`);
    if (!currentPane) return true;

    const requiredInputs = currentPane.querySelectorAll('[required]');
    let isValid = true;

    requiredInputs.forEach(input => {
      if (!input.value.trim()) {
        isValid = false;
        input.style.borderColor = 'var(--accent-rose)';
      } else {
        input.style.borderColor = 'var(--border-glass)';
      }
    });

    if (!isValid) {
      if (typeof showToast === 'function') {
        showToast('Please fill in all required fields in this section before proceeding.', 'info');
      }
    }
    return isValid;
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', () => {
      if (validateStep(currentStep)) {
        if (currentStep < totalSteps) updateStepView(currentStep + 1);
      }
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', () => {
      if (currentStep > 1) updateStepView(currentStep - 1);
    });
  }

  if (onboardingForm) {
    onboardingForm.addEventListener('submit', (e) => {
      for (let s = 1; s <= totalSteps; s++) {
        if (!validateStep(s)) {
          e.preventDefault();
          updateStepView(s);
          return false;
        }
      }
      return true;
    });
  }

  const skillNameInput = document.getElementById('skillNameInput');
  const skillLevelSelect = document.getElementById('skillLevelSelect');
  const addSkillBtn = document.getElementById('addSkillBtn');
  const skillsListContainer = document.getElementById('skillsListContainer');
  const noSkillsText = document.getElementById('noSkillsText');
  const skillsJsonInput = document.getElementById('skillsJsonInput');
  const quickSkillChips = document.querySelectorAll('.quick-skill-chip');

  let skillsList = [];

  function renderSkills() {
    if (skillsList.length === 0) {
      if (noSkillsText) noSkillsText.style.display = 'block';
      skillsListContainer.querySelectorAll('.tag-item').forEach(e => e.remove());
    } else {
      if (noSkillsText) noSkillsText.style.display = 'none';
      skillsListContainer.querySelectorAll('.tag-item').forEach(e => e.remove());

      skillsList.forEach((sk, idx) => {
        const tag = document.createElement('span');
        tag.className = 'tag-item';
        tag.innerHTML = `<strong>${sk.name}</strong> (${sk.level}) <span class="tag-remove-btn" data-idx="${idx}">×</span>`;
        skillsListContainer.appendChild(tag);
      });
    }

    if (skillsJsonInput) {
      skillsJsonInput.value = JSON.stringify(skillsList);
    }
  }

  function addSkill(name, level) {
    const cleanName = name.trim();
    if (!cleanName) return;

    if (skillsList.some(s => s.name.toLowerCase() === cleanName.toLowerCase())) {
      if (typeof showToast === 'function') showToast(`${cleanName} is already added.`, 'info');
      return;
    }

    skillsList.push({ name: cleanName, level: level || 'Intermediate' });
    renderSkills();
  }

  if (addSkillBtn && skillNameInput) {
    addSkillBtn.addEventListener('click', () => {
      const name = skillNameInput.value;
      const level = skillLevelSelect ? skillLevelSelect.value : 'Intermediate';
      if (name) {
        addSkill(name, level);
        skillNameInput.value = '';
      }
    });
  }

  quickSkillChips.forEach(chip => {
    chip.addEventListener('click', () => {
      const skillName = chip.getAttribute('data-skill');
      if (skillName) {
        addSkill(skillName, 'Intermediate');
        if (typeof showToast === 'function') showToast(`Added ${skillName} to your skills!`, 'success');
      }
    });
  });

  if (skillsListContainer) {
    skillsListContainer.addEventListener('click', (e) => {
      if (e.target.classList.contains('tag-remove-btn')) {
        const idx = parseInt(e.target.dataset.idx);
        skillsList.splice(idx, 1);
        renderSkills();
      }
    });
  }
});