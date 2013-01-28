/*
 * jQuery Check plugin
 * Version 1.0 (2011-02-16)
 * @requires jQuery v1.5.x or later
 *
 * Copyright (c) 2011 Andrius Semionovas
 * http://www.neworld.lt
*/

var NWCHECK_KEYS = Array();
var NW_CHECK_DEFAUL_CLASS = 'NW_CHECK_DEFAUL_CLASS';

jQuery.fn.check = function (key, cl) {
    if (NWCHECK_KEYS[key]) {
        NWCHECK_KEYS[key].removeClass(cl);
        NWCHECK_KEYS[key].removeClass(NW_CHECK_DEFAUL_CLASS);
    }
        
    NWCHECK_KEYS[key] = this;
    NWCHECK_KEYS[key].addClass(cl);
    NWCHECK_KEYS[key].addClass(NW_CHECK_DEFAUL_CLASS);
    return this;
}

jQuery.fn.autoCheck = function (key, cl) {
    this.click(function () {
        $(this).check(key,cl);
    });
    return this;
}

jQuery.isCheked = function (key) {
    return NWCHECK_KEYS[key].hasClass(NW_CHECK_DEFAUL_CLASS);
}

jQuery.getChecked = function (key) {
    return NWCHECK_KEYS[key];
}