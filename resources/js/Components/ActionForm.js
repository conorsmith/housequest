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
            if (!this.model.isSupportedAction()) {
                return;
            }

            if (this.model.action === "use"
                && e.detail.itemTypeId === "telephone"
            ) {
                return;
            }

            this.view.submit(
                this.model.createActionUrl(e.detail.itemId)
            );
        });

        window.EventBus.addEventListener("use.telephone", e => {
            this.view.set("number", e.detail.number);
            this.view.submit(
                this.model.createUseUrl(e.detail.itemId)
            )
        });
    }
}

class View {
    constructor(el) {
        this.el = el;
    }

    set(key, value) {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = value;
        this.el.append(input);
    }

    submit(actionUrl) {
        if (actionUrl === "") {
            return;
        }

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

    isSupportedAction() {
        return ["drop", "pick-up", "use", "eat"].includes(this.action);
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

    createUseUrl(itemId) {
        return "/" + this.gameId + "/use/" + itemId;
    }
}
