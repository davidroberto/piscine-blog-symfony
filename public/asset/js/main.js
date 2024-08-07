// je récupère tous les éléments qui ont pour classe "js-admin-article-delete" (mes boutons de suppression)
// je les stocke dans une variable
const deleteArticleButtons = document.querySelectorAll('.js-admin-article-delete');

// pour chaque bouton de suppression trouvé
deleteArticleButtons.forEach((deleteArticleButton) => {
    // on ajoute un event listener "click"
    // donc on attend que le bouton soit cliqué
    // quand il est cliqué, on execute une fonction de callback
    deleteArticleButton.addEventListener('click', () => {

        // récupère la valeur de l'attribut data-article-trigger-id
        // de l'élement cliqué
        const articleId = deleteArticleButton.dataset.articleTriggerId;

        // je sélectionné l'élement HTML qui contient un attribut nommé data-article-popup-target-id
        // et dont la valeur est la même que l'id de l'article
        const popup = document.querySelector(`[data-article-popup-target-id="${articleId}"]`);

        // je passe la popup trouvée en display block
        popup.style.display = "block";
    });
})
