export default class Item {
    constructor(id, typeId, label, state, isContainer, whereaboutsId, whereaboutsType) {
        this.id = id;
        this.typeId = typeId;
        this.label = label;
        this.state = state;
        this.isContainer = isContainer;
        this.whereaboutsId = whereaboutsId;
        this.whereaboutsType = whereaboutsType;
    }
}
