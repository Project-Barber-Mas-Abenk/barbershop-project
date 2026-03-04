<?php
/* 
  Reusable Modal Component
  Usage:
  <?php include '../components/ui/modal.php'; ?>
*/
?>

<div id="app-modal" class="modal">
    <div class="modal__overlay"></div>

    <div class="modal__container">
        <div class="modal__icon" id="modalIcon">
            <i class="fa-solid fa-check"></i>
        </div>

        <h3 class="modal__title" id="modalTitle">
            Title
        </h3>

        <p class="modal__message" id="modalMessage">
            Message
        </p>

        <button class="modal__button" id="modalButton">
            Lanjut
        </button>
    </div>
</div>

<script>
(function () {

    const modal = document.getElementById("app-modal");
    const title = document.getElementById("modalTitle");
    const message = document.getElementById("modalMessage");
    const button = document.getElementById("modalButton");
    const icon = document.getElementById("modalIcon");

    function setType(type) {
        modal.classList.remove("modal--success", "modal--error", "modal--info");

        if (type === "error") {
            modal.classList.add("modal--error");
            icon.innerHTML = '<i class="fa-solid fa-xmark"></i>';
        } 
        else if (type === "info") {
            modal.classList.add("modal--info");
            icon.innerHTML = '<i class="fa-solid fa-circle-info"></i>';
        } 
        else {
            modal.classList.add("modal--success");
            icon.innerHTML = '<i class="fa-solid fa-check"></i>';
        }
    }

    window.showModal = function ({
        type = "success",
        titleText = "Berhasil",
        messageText = "",
        buttonText = "Lanjut",
        onConfirm = null
    }) {
        setType(type);

        title.textContent = titleText;
        message.textContent = messageText;
        button.textContent = buttonText;

        modal.classList.add("active");

        button.onclick = function () {
            modal.classList.remove("active");
            if (onConfirm) onConfirm();
        };
    };

})();
</script>