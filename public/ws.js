var w = new WebSocket('ws://local.rd-stuff.lpu8er.org:8080/');
w.onopen = function(e) {
    e.send('Hello World');
};

w.onmessage = function(e) {
    console.log(e.data);
    document.getElementById('log').innerHTML += e.data+'<br />';
};
