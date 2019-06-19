require('../css/app.scss');

require('bootstrap');
require('@coreui/coreui');

const Centrifuge = require('centrifuge');

document.addEventListener('DOMContentLoaded', function () {
    const centrifuge = new Centrifuge('ws://localhost:8083/connection/websocket');
    centrifuge.subscribe('alerts', function (message) {
        console.log(message.data.message);
    });
    centrifuge.connect();
});