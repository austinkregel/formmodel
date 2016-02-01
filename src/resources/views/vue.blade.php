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
                $i = 0;
            foreach ($components as $c) {
                echo "\t".$c.': \'\''.((($count - 1) == $i) ? '' : ',')."\n";
                ++$i;
            }?>
        }
    };
    new Vue({
        el: "#vue-form-wrapper",
        data: data,
        methods: {
            makeRequest: function (e) {
                e.preventDefault();
                checkInput(this.$data.data);

                request(e.target.action,
                        this.$data.data
                        , function(responseArea){
                    if(responseArea.classList.contains('alert')){
                        responseArea.className += 'alert-success ';
                        responseArea.className = responseArea.className.replace(/\balert-.*\s/g, ' alert-success');
                        $(form).parent().parent().parent().remove();
                    }
                }, function(responseArea){
                    if(responseArea.classList.contains('alert')){
                        responseArea.className += 'alert-warning ';
                        responseArea.className = respArea.className.replace(/\balert-.*\s/g, ' alert-warning');
                    }
                }, function(responseArea){
                    if(responseArea.classList.contains('alert')){
                        responseArea.className += 'alert-danger ';
                        responseArea.className = responseArea.className.replace(/\balert-.*\s/g, ' alert-danger');
                    }
                });
            },
            close: function (e) {
                this.response = '';
            },
            checkInputs: function (data){

            }
        },
        ready: function(){
            setTimeout(function(){
                $('select').material_select();
            },100);
        }
    });
    @include('formmodel::request', ['type' => $type])
</script>
