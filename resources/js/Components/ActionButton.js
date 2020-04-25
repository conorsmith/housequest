import EventBus from "../EventBus";

export default class Controller {
    static fromAction(action) {
        const el = document.querySelector(".js-" + action);
        return new Controller(
            new Model(
                el.dataset.defaultAction,
                el.dataset.defaultMultipleAction,
                el.dataset.altAction,
                el.dataset.altMultipleAction
            ),
            new View(el)
        );
    }

    constructor(model, view) {
        this.model = model;
        this.view = view;

        this.view.el.addEventListener("click", e => {
            this.model.toggle();

            if (this.model.isButtonActive) {
                window.EventBus.dispatchEvent("actionBtn.selected", {button: this.model.defaultAction});
            } else {
                window.EventBus.dispatchEvent("actionBtn.deselected");
            }
        });

        window.EventBus.addEventListener("action.changed", e => { this.model.handleActionChange(e.detail.action); });
        window.EventBus.addEventListener("alt.activated", e => { this.model.setAltActive(); });
        window.EventBus.addEventListener("alt.deactivated", e => { this.model.setAltInactive(); });

        window.EventBus.addEventListener("action.completed", e => { this.model.deactivateButton(); });
        window.EventBus.addEventListener("cancel", e => { this.model.deactivateButton(); });

        this.model.bus.addEventListener("action.activated", e => { this.view.activateButton(); });
        this.model.bus.addEventListener("action.deactivated", e => { this.view.deactivateButton(); });

        this.model.bus.addEventListener("alt.activated", e => { this.view.activateAlt(); });
        this.model.bus.addEventListener("alt.deactivated", e => { this.view.deactivateAlt(); });
    }
}

class View {
    constructor(el) {
        this.el = el;
    }

    activateButton() {
        this.el.classList.add("selected");
    }

    deactivateButton() {
        this.el.classList.remove("selected");
    }

    activateAlt() {
        if (this.el.dataset.altAction !== "place") {
            this.el.disabled = true;
            document.querySelector(".js-make").disabled = true;
            return;
        }

        this.originalLabel = this.el.innerHTML;
        if (this.el.dataset.altLabel !== undefined) {
            this.el.innerHTML = this.el.dataset.altLabel;
        }
    }

    deactivateAlt() {
        if (this.el.dataset.altAction !== "place") {
            this.el.disabled = false;
            document.querySelector(".js-make").disabled = false;
            return;
        }

        this.el.innerHTML = this.originalLabel;
    }
}

class Model {
    constructor(defaultAction, defaultMultipleAction, altAction, altMultipleAction) {
        this.defaultAction = defaultAction;
        this.defaultMultipleAction = defaultMultipleAction;
        this.altAction = altAction;
        this.altMultipleAction = altMultipleAction;
        this.isButtonActive = false;
        this.isAlt = false;

        this.bus = new EventBus();
    }

    toggle() {
        if (this.isButtonActive) {
            this.deactivateButton();
        } else {
            this.activateButton();
        }
    }

    handleActionChange(action) {
        if (action !== this.defaultAction
            && action !== this.defaultMultipleAction
            && action !== this.altAction
            && action !== this.altMultipleAction
        ) {
            this.deactivateButton();
        }
    }

    activateButton() {
        this.isButtonActive = true;
        this.bus.dispatchEvent("action.activated", { action: this.defaultAction });
    }

    deactivateButton() {
        this.isButtonActive = false;
        this.bus.dispatchEvent("action.deactivated");
    }

    setAltActive() {
        this.isAlt = true;
        this.bus.dispatchEvent("alt.activated");
    }

    setAltInactive() {
        this.isAlt = false;
        this.bus.dispatchEvent("alt.deactivated");
    }
}
