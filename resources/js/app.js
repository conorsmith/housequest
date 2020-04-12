
document.querySelectorAll(".js-increment").forEach(function (buttonEl) {
    buttonEl.addEventListener("click", function (e) {
        e.preventDefault();
        let itemEl = buttonEl.closest(".js-item");
        let selectedQuantityEl = itemEl.querySelector(".js-selected-quantity");
        let selectedQuantity = parseInt(itemEl.dataset.selectedQuantity, 10);
        let availableQuantity = parseInt(itemEl.dataset.availableQuantity, 10);

        if (selectedQuantity === availableQuantity) {
            return;
        }

        if (selectedQuantity === 0) {
            itemEl.classList.add("active");
            selectedQuantityEl.classList.add("badge-warning");
            selectedQuantityEl.classList.remove("badge-light")
        }

        itemEl.dataset.selectedQuantity = selectedQuantity + 1;

        selectedQuantityEl.innerHTML = itemEl.dataset.selectedQuantity;
        itemEl.querySelector(".js-quantity-input").value = itemEl.dataset.selectedQuantity;
    });
});

document.querySelectorAll(".js-decrement").forEach(function (buttonEl) {
    buttonEl.addEventListener("click", function (e) {
        e.preventDefault();
        let itemEl = buttonEl.closest(".js-item");
        let selectedQuantityEl = itemEl.querySelector(".js-selected-quantity");
        let selectedQuantity = parseInt(itemEl.dataset.selectedQuantity, 10);
        let selectedPortions = parseInt(itemEl.dataset.selectedPortions, 10);

        if (selectedQuantity === 0) {
            return;
        }

        if (selectedQuantity === 1 && selectedPortions === 0) {
            itemEl.classList.remove("active");
        }

        if (selectedQuantity === 1) {
            selectedQuantityEl.classList.add("badge-light");
            selectedQuantityEl.classList.remove("badge-warning");
        }

        itemEl.dataset.selectedQuantity = selectedQuantity - 1;

        selectedQuantityEl.innerHTML = itemEl.dataset.selectedQuantity;
        itemEl.querySelector(".js-quantity-input").value = itemEl.dataset.selectedQuantity;
    });
});

document.querySelectorAll(".js-take-all").forEach(function (buttonEl) {
    buttonEl.addEventListener("click", function (e) {
        e.preventDefault();
        let itemEl = buttonEl.closest(".js-item");
        let selectedQuantityEl = itemEl.querySelector(".js-selected-quantity");
        let selectedQuantity = parseInt(itemEl.dataset.selectedQuantity, 10);
        let availableQuantity = parseInt(itemEl.dataset.availableQuantity, 10);

        if (selectedQuantity === availableQuantity) {
            return;
        }

        if (selectedQuantity === 0) {
            itemEl.classList.add("active");
            selectedQuantityEl.classList.add("badge-warning");
            selectedQuantityEl.classList.remove("badge-light")
        }

        itemEl.dataset.selectedQuantity = availableQuantity;

        selectedQuantityEl.innerHTML = itemEl.dataset.selectedQuantity;
        itemEl.querySelector(".js-quantity-input").value = itemEl.dataset.selectedQuantity;
    });
});


document.querySelectorAll(".js-portion-increment").forEach(function (buttonEl) {
    buttonEl.addEventListener("click", function (e) {
        e.preventDefault();
        let itemEl = buttonEl.closest(".js-item");
        let selectedPortionsEl = itemEl.querySelector(".js-selected-portions");
        let unselectedPortionsEl = itemEl.querySelector(".js-unselected-portions");
        let totalPortions = parseInt(itemEl.dataset.totalPortions, 10);
        let selectedPortions = parseInt(itemEl.dataset.selectedPortions, 10);
        let availablePortions = parseInt(itemEl.dataset.availablePortions, 10);

        if (selectedPortions === availablePortions) {
            return;
        }

        if (selectedPortions === 0) {
            itemEl.classList.add("active");
        }

        itemEl.dataset.selectedPortions = selectedPortions + 1;

        selectedPortionsEl.style.width = (itemEl.dataset.selectedPortions / totalPortions * 100) + "%";
        unselectedPortionsEl.style.width = ((availablePortions - itemEl.dataset.selectedPortions) / totalPortions * 100) + "%";

        itemEl.querySelector(".js-portions-input").value = itemEl.dataset.selectedPortions;
    });
});

document.querySelectorAll(".js-portion-decrement").forEach(function (buttonEl) {
    buttonEl.addEventListener("click", function (e) {
        e.preventDefault();
        let itemEl = buttonEl.closest(".js-item");
        let selectedPortionsEl = itemEl.querySelector(".js-selected-portions");
        let unselectedPortionsEl = itemEl.querySelector(".js-unselected-portions");
        let totalPortions = parseInt(itemEl.dataset.totalPortions, 10);
        let selectedPortions = parseInt(itemEl.dataset.selectedPortions, 10);
        let availablePortions = parseInt(itemEl.dataset.availablePortions, 10);
        let selectedQuantity = parseInt(itemEl.dataset.selectedQuantity, 10);

        if (selectedPortions === 0) {
            return;
        }

        if (selectedPortions === 1 && selectedQuantity === 0) {
            itemEl.classList.remove("active");
        }

        itemEl.dataset.selectedPortions = selectedPortions - 1;

        selectedPortionsEl.style.width = (itemEl.dataset.selectedPortions / totalPortions * 100) + "%";
        unselectedPortionsEl.style.width = ((availablePortions - itemEl.dataset.selectedPortions) / totalPortions * 100) + "%";

        itemEl.querySelector(".js-portions-input").value = itemEl.dataset.selectedPortions;
    });
});

import EventBus from "./EventBus";
import ActionButtonController from "./Components/ActionButton";
import ActionFormController from "./Components/ActionForm";
import InventoryItemController from "./Components/InventoryItem";

window.EventBus = new EventBus();

ActionFormController.fromFormEl(document.querySelector("#js-action"));

ActionButtonController.fromAction("pick-up");
ActionButtonController.fromAction("drop");
ActionButtonController.fromAction("use");
ActionButtonController.fromAction("eat");

document.querySelectorAll(".js-inventory-item").forEach(function (itemEl) {
    InventoryItemController.fromItemEl(itemEl);
});
