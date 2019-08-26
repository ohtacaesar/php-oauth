window.$ = window.jQuery = require('jquery');

require('popper.js');
require('bootstrap');

import '../css/index.css';

document.onkeypress = function () {
  if (window.event.keyCode === 13) {
    return false;
  }
};

