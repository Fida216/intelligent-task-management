

document.addEventListener("DOMContentLoaded", function () {
    // Sélection des éléments
    const avatarLarge = document.querySelector(".avatar-large");
    const changeAvatarBtn = document.querySelector(".change-avatar-btn");
    const avatarUpload = document.getElementById("avatar-upload");
    const deleteAvatarBtn = document.querySelector(".btn.btn-text");
  
    // Récupérer les données de profil depuis localStorage
    const firstName = localStorage.getItem("userFirstName");
    const lastName = localStorage.getItem("userLastName");
    const email = localStorage.getItem("userEmail");
    const creationDate = localStorage.getItem("userCreationDate");
    const avatar = localStorage.getItem("userAvatar"); // Si l'avatar est stocké dans localStorage
  
    // Remplir les informations du profil
    document.getElementById("first-name").innerText = firstName || "Prénom non défini";
    document.getElementById("last-name").innerText = lastName || "Nom non défini";
    document.getElementById("email").innerText = email || "Email non défini";
    document.getElementById("creation-date").innerText = creationDate || "Date non définie";
  
    // Avatar : Affichage et gestion
    if (avatar) {
      avatarLarge.style.backgroundImage = `url(${avatar})`;
    }
  
    // Gestion de l'avatar
    changeAvatarBtn.addEventListener("click", function () {
      avatarUpload.click();
    });
  
    avatarUpload.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (!file) return;
  
      if (!file.type.match("image.*")) {
        alert("Veuillez sélectionner une image valide.");
        return;
      }
  
      if (file.size > 2 * 1024 * 1024) { // Limite de taille de 2MB
        alert("L'image ne doit pas dépasser 2MB.");
        return;
      }
  
      const reader = new FileReader();
      reader.onload = function (e) {
        avatarLarge.style.backgroundImage = `url(${e.target.result})`;
        localStorage.setItem("userAvatar", e.target.result); // Stockage de l'avatar dans localStorage
      };
      reader.readAsDataURL(file);
    });
  
    // Suppression de l'avatar
    deleteAvatarBtn.addEventListener("click", function () {
      avatarLarge.style.backgroundImage = "none"; // Réinitialiser l'avatar
      localStorage.removeItem("userAvatar"); // Supprimer l'avatar du localStorage
      alert("L'avatar a été supprimé.");
    });
  
  });