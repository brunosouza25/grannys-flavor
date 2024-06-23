function justNumber(text) {
    return text.parseFloat(text.match(/-?(?:\d+(?:\.\d*)?|\.\d+)/)[0]).toFixed(2);
}