import EventBus from "../EventBus";

export default class Controller {
    static fromEl(el) {
        return new Controller(
            new Model(),
            new View(el)
        );
    }

    constructor(model, view) {
        this.model = model;
        this.view = view;

        this.view.onHide(e => {
            this.model.close();
            window.EventBus.dispatchEvent("action.completed", {
                action: this.model.action,
                itemId: this.model.itemId
            });
        });

        this.view.onKeypadPress(e => {
            this.model.appendNumber(e.target.dataset.symbol);
        });

        this.view.onCall(e => {
            window.EventBus.dispatchEvent("use.telephone", {
                itemId: this.model.itemId,
                number: this.model.number
            });
        });

        window.EventBus.addEventListener("action.changed", e => { this.model.action = e.detail.action; });

        window.EventBus.addEventListener("item.selected", e => {
            this.model.open(e.detail.item.id, e.detail.item.typeId);
        });

        this.model.bus.addEventListener("opened", e => { this.view.open(); });
        this.model.bus.addEventListener("number.updated", e => { this.view.renderNumber(e.detail.number); });
        this.model.bus.addEventListener("closed", e => { this.view.close(); });
    }
}

class View {
    constructor(el) {
        this.el = el;
        this.$el = $(this.el);
    }

    open() {
        this.$el.modal('show');
    }

    close() {
        this.el.querySelector(".number-display").innerHTML = "";
    }

    renderNumber(number) {
        this.el.querySelector(".number-display").innerHTML = number;
    }

    onHide(callback) {
        this.$el.on("hide.bs.modal", callback);
    }

    onKeypadPress(callback) {
        this.el.querySelectorAll(".keypad button").forEach(el => {
            el.addEventListener("click", callback);
        });
    }

    onCall(callback) {
        this.el.querySelector(".call-button").addEventListener("click", callback);
    }
}

class Model {
    constructor() {
        this.itemId = null;
        this.isOpen = false;
        this.action = null;
        this.number = "";

        this.bus = new EventBus();
    }

    open(itemId, itemTypeId) {
        if (this.action !== "use") {
            return;
        }

        if (itemTypeId !== "telephone") {
            return;
        }

        this.itemId = itemId;
        this.isOpen = true;
        this.bus.dispatchEvent("opened");
    }

    appendNumber(symbol) {
        if (this.number.length >= 80) {
            return;
        }
        this.number += symbol;
        this.bus.dispatchEvent("number.updated", { number: this.number })
    }

    close() {
        this.isOpen = false;
        this.number = "";
        this.bus.dispatchEvent("closed");
    }
}
