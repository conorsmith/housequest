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
                window.EventBus.dispatchEvent("actionBtn.selected", { actions: this.model.getActions() });
            } else {
                window.EventBus.dispatchEvent("actionBtn.deselected");
            }
        });

        window.EventBus.addEventListener("action.changed", e => { this.model.handleActionChange(e.detail.action); });
        window.EventBus.addEventListener("alt.activated", e => { this.model.setAltMode(); });
        window.EventBus.addEventListener("alt.deactivated", e => { this.model.unsetAltMode(); });
        window.EventBus.addEventListener("mul.activated", e => { this.model.setMulMode(); });
        window.EventBus.addEventListener("mul.deactivated", e => { this.model.unsetMulMode(); });

        window.EventBus.addEventListener("action.completed", e => { this.model.deactivateButton(); });
        window.EventBus.addEventListener("cancel", e => {
            this.model.deactivateButton();
            this.model.setAltInactive();
        });

        this.model.bus.addEventListener("action.activated", e => { this.view.activateButton(); });
        this.model.bus.addEventListener("action.deactivated", e => { this.view.deactivateButton(); });

        this.model.bus.addEventListener("mode.alt.active", e => { this.view.activateAlt(); });
        this.model.bus.addEventListener("mode.alt.inactive", e => { this.view.deactivateAlt(e.detail.mulMode); });
        this.model.bus.addEventListener("mode.mul.active", e => { this.view.activateMul(); });
        this.model.bus.addEventListener("mode.mul.inactive", e => { this.view.deactivateMul(e.detail.altMode); });
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
        if (this.el.dataset.altAction === undefined) {
            this.el.disabled = true;
            document.querySelector(".js-make").disabled = true;
            return;
        }

        this.originalLabel = this.el.innerHTML;
        if (this.el.dataset.altLabel !== undefined) {
            this.el.innerHTML = this.el.dataset.altLabel;
        }
    }

    deactivateAlt(mulMode) {
        if (this.el.dataset.altAction === undefined) {
            if (mulMode === false) {
                this.el.disabled = false;
                document.querySelector(".js-make").disabled = false;
            }
            return;
        }

        if (this.originalLabel !== undefined) {
            this.el.innerHTML = this.originalLabel;
        }
    }

    activateMul() {
        if (this.el.dataset.defaultMultipleAction === undefined) {
            this.el.disabled = true;
            document.querySelector(".js-make").disabled = true;
        }
    }

    deactivateMul(altMode) {
        if (this.el.dataset.defaultMultipleAction === undefined
            && altMode === false
        ) {
            this.el.disabled = false;
            document.querySelector(".js-make").disabled = false;
        }
    }
}

class Model {
    constructor(defaultAction, defaultMultipleAction, altAction, altMultipleAction) {
        this.defaultAction = defaultAction;
        this.defaultMultipleAction = defaultMultipleAction;
        this.altAction = altAction;
        this.altMultipleAction = altMultipleAction;
        this.isButtonActive = false;
        this.altMode = false;
        this.mulMode = false;

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

    getActions() {
        return {
            defaultAction: this.defaultAction,
            defaultMultipleAction: this.defaultMultipleAction,
            altAction: this.altAction,
            altMultipleAction: this.altMultipleAction
        };
    }

    activateButton() {
        this.isButtonActive = true;
        this.bus.dispatchEvent("action.activated", { action: this.defaultAction });
    }

    deactivateButton() {
        this.isButtonActive = false;
        this.bus.dispatchEvent("action.deactivated");
    }

    setAltMode() {
        this.altMode = true;
        this.bus.dispatchEvent("mode.alt.active");
    }

    unsetAltMode() {
        this.altMode = false;
        this.bus.dispatchEvent("mode.alt.inactive", {
            mulMode: this.mulMode
        });
    }

    setMulMode() {
        this.mulMode = true;
        this.bus.dispatchEvent("mode.mul.active");
    }

    unsetMulMode() {
        this.mulMode = false;
        this.bus.dispatchEvent("mode.mul.inactive", {
            altMode: this.altMode
        });
    }
}
