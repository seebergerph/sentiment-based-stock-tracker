module.exports = class StockTopics {

    constructor(symbol) {
        this._symbol = symbol;
        this._topics = new Set();
    }

    get symbol() {
        return this._symbol;
    }

    get topics() {
        return this._topics;
    }

    addTopic(topic) {
        this._topics.add(topic);
    }
}