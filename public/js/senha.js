// Confere "senha" e "confirmacao" enquanto a pessoa digita e bloqueia o envio se forem diferentes.
// É só conforto: o PHP valida de novo no servidor.
document.querySelectorAll('input[name="confirmacao"]').forEach(function (confirmacao) {
    var senha = confirmacao.form.querySelector('input[name="senha"]');
    var aviso = document.createElement("span");
    var mensagem = "As senhas não são iguais.";

    aviso.setAttribute("role", "alert");
    aviso.style.color = "#b00020";
    aviso.style.marginLeft = "8px";
    confirmacao.insertAdjacentElement("afterend", aviso);

    function conferir() {
        var diferente = confirmacao.value !== "" && confirmacao.value !== senha.value;

        confirmacao.setCustomValidity(diferente ? mensagem : "");
        aviso.textContent = diferente ? mensagem : "";
    }

    senha.addEventListener("input", conferir);
    confirmacao.addEventListener("input", conferir);
});
