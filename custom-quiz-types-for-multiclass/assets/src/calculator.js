jQuery(document).ready(function($) {
    $.calculator.addKeyDef('_÷', '÷', $.calculator.binary, $.calculator._divide, 'arith divide', 'DIVIDE', '÷');

    $('.mc-calculator .wpProQuiz_questionInput').calculator({
        layout: [$.calculator.SQRT+'BSCECA', '_7_8_9_÷@X', '_4_5_6_*@U', '_1_2_3_-@E', '_0_._=_+'],
        showOn: 'button',
        buttonImageOnly: true,
        buttonImage: customQuizAjax.calculatorIcon,
        buttonText: 'Calculator',
        constrainInput: false,
        decimalChar: '.',
            buttonText: '...', buttonStatus: 'Taschenrechner öffnen',
            closeText: 'Schliessen', closeStatus: 'Beendet den Taschenrechner',
            useText: 'Übernehmen', useStatus: 'Benutzt den aktuellen Wert',
            eraseText: 'Löschen', eraseStatus: 'Löscht den Inhalt des Feldes',
            backspaceText: 'R', backspaceStatus: 'Löscht die letzte Zahl',
            clearErrorText: 'C', clearErrorStatus: 'Löscht die Eingabe',
            clearText: 'CA', clearStatus: 'Resettet die komplette Eingabe',
            memClearText: 'MC', memClearStatus: 'Löscht den Speicher',
            memRecallText: 'MR', memRecallStatus: 'Holt den Speicher zurück',
            memStoreText: 'MS', memStoreStatus: 'Speichert den aktuellen Wert',
            memAddText: 'M+', memAddStatus: 'Addiert den aktuellen Wert in den Speicher',
            memSubtractText: 'M-', memSubtractStatus: 'Subtrahiert den Wert vom aktuellen Speicher', 
            base2Text: 'Bin', base2Status: 'Wechselt zu Binär',
            base8Text: 'Okt', base8Status: 'Wechselt zu Oktal',
            base10Text: 'Dez', base10Status: 'Wechselt zu Dezimal',
            base16Text: 'Hex', base16Status: 'Wechselt zu Hexadezimal',
            degreesText: 'Deg', degreesStatus: 'Wechselt zu Grad',
            radiansText: 'Rad', radiansStatus: 'Wechselt zu Radianten',
            isRTL: false
            
    });

    (function($) { // hide the namespace
        $.calculator.regionalOptions['de'] = {
            decimalChar: '.',
            buttonText: '...', buttonStatus: 'Taschenrechner öffnen',
            closeText: 'Schliessen', closeStatus: 'Beendet den Taschenrechner',
            useText: 'Übernehmen', useStatus: 'Benutzt den aktuellen Wert',
            eraseText: 'Löschen', eraseStatus: 'Löscht den Inhalt des Feldes',
            backspaceText: 'R', backspaceStatus: 'Löscht die letzte Zahl',
            clearErrorText: 'C', clearErrorStatus: 'Löscht die Eingabe',
            clearText: 'CA', clearStatus: 'Resettet die komplette Eingabe',
            memClearText: 'MC', memClearStatus: 'Löscht den Speicher',
            memRecallText: 'MR', memRecallStatus: 'Holt den Speicher zurück',
            memStoreText: 'MS', memStoreStatus: 'Speichert den aktuellen Wert',
            memAddText: 'M+', memAddStatus: 'Addiert den aktuellen Wert in den Speicher',
            memSubtractText: 'M-', memSubtractStatus: 'Subtrahiert den Wert vom aktuellen Speicher', 
            base2Text: 'Bin', base2Status: 'Wechselt zu Binär',
            base8Text: 'Okt', base8Status: 'Wechselt zu Oktal',
            base10Text: 'Dez', base10Status: 'Wechselt zu Dezimal',
            base16Text: 'Hex', base16Status: 'Wechselt zu Hexadezimal',
            degreesText: 'Deg', degreesStatus: 'Wechselt zu Grad',
            radiansText: 'Rad', radiansStatus: 'Wechselt zu Radianten',
            isRTL: false};
        $.calculator.setDefaults($.calculator.regionalOptions['de']);
    })(jQuery);    
});