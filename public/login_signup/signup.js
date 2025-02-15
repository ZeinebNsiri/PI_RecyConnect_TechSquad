document.addEventListener("DOMContentLoaded", function () {
    let radios = document.querySelectorAll("input[name='typeUtilisateur']");

    radios.forEach(radio => {
        radio.addEventListener("change", function () {
            let selectedType = this.value;
            window.location.href = registerRoute + "?type=" + selectedType;
        });
    });
});
