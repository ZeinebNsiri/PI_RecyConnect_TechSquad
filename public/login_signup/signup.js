 document.addEventListener("DOMContentLoaded", function () {
        const typeUtilisateur = document.querySelector('input[name="registration_form[typeUtilisateur]"]:checked');
        const matriculeGroup = document.getElementById('matriculeGroup');
        const prenomGroup = document.getElementById('prenomGroup');
        const nomGroup = document.getElementById('nomGroup');

        function toggleFields() {
            if (document.querySelector('input[name="registration_form[typeUtilisateur]"]:checked').value === 'professionnel') {
                matriculeGroup.style.display = 'block';
                prenomGroup.style.display = 'none';
                nomGroup.classList.remove('col-md-6');
                nomGroup.classList.add('col-md-12');
            } else {
                matriculeGroup.style.display = 'none';
                prenomGroup.style.display = 'block';
                nomGroup.classList.remove('col-md-12');
                nomGroup.classList.add('col-md-6');
            }
        }

        document.querySelectorAll('input[name="registration_form[typeUtilisateur]"]').forEach(radio => {
            radio.addEventListener('change', toggleFields);
        });

        toggleFields();
    });