document.addEventListener("DOMContentLoaded", function() {
    const option1 = document.getElementById('form_typeUtilisateur_0');
    const option2 = document.getElementById('form_typeUtilisateur_1');
    const matriculeGroup = document.getElementById('matriculeGroup');
    const prenomGroup = document.getElementById('prenomGroup');
    const nomGroup = document.getElementById('nomGroup');
    const socialButtonsGroup = document.getElementById('socialButtonsGroup');
    const socialButtonsGroup2 = document.getElementById('socialButtonsGroup2');

    function updateForm() {
        if (option1.checked) {
            matriculeGroup.style.display = 'none';
            prenomGroup.style.display = 'block';
            socialButtonsGroup.style.display = 'flex';
            socialButtonsGroup2.style.display = 'flex';
            nomGroup.classList.remove('col-md-12');
            nomGroup.classList.add('col-md-6');
        } else if (option2.checked) {
            matriculeGroup.style.display = 'block';
            prenomGroup.style.display = 'none';
            socialButtonsGroup.style.display = 'none';
            socialButtonsGroup2.style.display = 'none';
            nomGroup.classList.remove('col-md-6');
            nomGroup.classList.add('col-md-12');
        }
    }

    option1.addEventListener('change', updateForm);
    option2.addEventListener('change', updateForm);
    updateForm();
});
