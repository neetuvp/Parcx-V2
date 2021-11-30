jsPDF.API.autoText = function (text, x, y, halign, valign) {
    if (typeof x !== 'number' || typeof y !== 'number') {
        console.error('The x and y parameters are required. Missing for the text: ', text);
    }
    let k = this.internal.scaleFactor;
    let fontSize = this.internal.getFontSize() / k;

    let splitRegex = /\r\n|\r|\n/g;
    let splitText = null;
    let lineCount = 1;
    if (valign === 'middle' || valign === 'bottom' || halign === 'center' || halign === 'right') {
        splitText = typeof text === 'string' ? text.split(splitRegex) : text;

        lineCount = splitText.length || 1;
    }

    // Align the top
    y += fontSize * (2 - FONT_ROW_RATIO);

    if (valign === 'middle')
        y -= (lineCount / 2) * fontSize * FONT_ROW_RATIO;
    else if (valign === 'bottom')
        y -= lineCount * fontSize * FONT_ROW_RATIO;

    if (halign === 'center' || halign === 'right') {
        let alignSize = fontSize;
        if (halign === 'center')
            alignSize *= 0.5;

        if (lineCount >= 1) {
            for (let iLine = 0; iLine < splitText.length; iLine++) {
                this.text(splitText[iLine], x - this.getStringUnitWidth(splitText[iLine]) * alignSize, y);
                y += fontSize;
            }
            return this;
        }
        x -= this.getStringUnitWidth(text) * alignSize;
    }

    this.text(text, x, y);

    return this;
};