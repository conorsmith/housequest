export default class Controller {
    static fromFormEl(formEl) {
        return new Controller(
            new Model(
                formEl.dataset.gameId,
                formEl.dataset.currentLocationId
            ),
            new View(formEl)
        );
    }

    constructor(model, view) {
        this.model = model;
        this.view = view;

        window.EventBus.addEventListener("action.selected", e => {
            this.model.action = e.detail.action;
        });

        window.EventBus.addEventListener("action.triggered", e => {
            if (this.model.action === null) {
                return;
            }

            this.view.submit(
                this.model.createActionUrl(e.detail.itemId)
            );
        });
    }
}

class View {
    constructor(el) {
        this.el = el;
    }

    submit(actionUrl) {
        this.el.action = actionUrl;
        this.el.submit();
    }
}

class Model {
    constructor(gameId, currentLocationId) {
        this.gameId = gameId;
        this.currentLocationId = currentLocationId;
        this.action = null;
    }

    createActionUrl(itemId) {
        if (this.action === "drop") {
            return "/" + this.gameId + "/drop/" + itemId + "/" + this.currentLocationId;
        }

        if (this.action === "pick-up") {
            return "/" + this.gameId + "/pick-up/" + itemId;
        }

        if (this.action === "use") {
            return "/" + this.gameId + "/use/" + itemId;
        }

        if (this.action === "eat") {
            return "/" + this.gameId + "/eat/" + itemId;
        }

        console.error("Cannot create action URL for action: " + this.action);
        return "";
    }
}
