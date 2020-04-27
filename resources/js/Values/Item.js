export default class Item {
    constructor(id, typeId, label, isContainer, whereaboutsId, whereaboutsType) {
        this.id = id;
        this.typeId = typeId;
        this.label = label;
        this.isContainer = isContainer;
        this.whereaboutsId = whereaboutsId;
        this.whereaboutsType = whereaboutsType;
    }
}
