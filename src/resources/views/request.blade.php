function request(url, data, success, nochange, fail) {
    var xmlhttp = new XMLHttpRequest();   // new HttpRequest instance
    xmlhttp.open("{{$type}}", url);

    xmlhttp.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xmlhttp.setRequestHeader("X-Requested-With", 'XMLHttpRequest');
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4) {
            var response = JSON.parse(xmlhttp.responseText);
            vm.$data.response = response.message;
            var respArea = document.getElementById('response');
            if (!respArea.classList.contains('alert)')) {
                respArea.className = 'alert ';
            }
            switch (response.code) {
                case 200:
                case 202:
                    success(respArea);
                    break;
                case 205:
                    nochange(respArea);
                    break;
                default:
                    fail(respArea);
            }
        }
    };
    xmlhttp.send(JSON.stringify(data));
}