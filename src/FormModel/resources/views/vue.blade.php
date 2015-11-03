<script>
    var data = {
        response: '',
        debug:'',
        data: {
            @if(config('kregel.formmodel.using.csrf'))
            _token:"{{csrf_token()}}",
            @endif
            <?php
                $count = count($components);
                $i =0;
                foreach($components as $c){
                    echo "\t".$c.': \'\''.((($count - 1) == $i)?'':','). "\n";
                    $i++;
                }?>
        }
    };
    var vm = new Vue({
        el: "#vue-form-wrapper",
        data : data,
        methods: {
            makeRequest: function (e) {
                request(e.target.action, this.$data.data);
            },
            close: function(e){
                this.response ='';
            }
        }
    });
    function request(url, data){
        var xmlhttp = new XMLHttpRequest();   // new HttpRequest instance
        xmlhttp.open("{{$type}}", url);

        xmlhttp.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        xmlhttp.setRequestHeader("X-Requested-With", 'XMLHttpRequest');
        xmlhttp.onreadystatechange =  function () {
            if (xmlhttp.readyState == 4) {
                var response = JSON.parse(xmlhttp.responseText);
                vm.$data.response = response.message;
                var respArea = document.getElementById('response'),
                    classes;
                if(!respArea.classList.contains('alert)')){
                    respArea.className = 'alert ';
                }
                switch(response.code){
                    case 200:
                    case 202:
                        if(respArea.classList.contains('alert')){
                            respArea.className += 'alert-success ';
                            respArea.className = respArea.className.replace(/\balert-.*\s/g, ' alert-success');
                        }
                        break;
                    case 205:
                        if(respArea.classList.contains('alert')){
                            respArea.className += 'alert-warning ';
                            respArea.className = respArea.className.replace(/\balert-.*\s/g, ' alert-warning');
                        }
                        break;
                    default:
                        if(respArea.classList.contains('alert')){
                            respArea.className += 'alert-danger ';
                            respArea.className = respArea.className.replace(/\balert-.*\s/g, ' alert-danger');
                        }
                }
            }
        };
        xmlhttp.send(JSON.stringify(data));
    }
</script>