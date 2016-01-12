<div id="vue-form-wrapper">
    <div id ="response" v-show="response">
        @{{ response }}
        <div class="close" @click="close">&times;</div>
    </div>
    {!! $form !!}
</div>
@include('formmodel::vue', ['vue_components' => $vue_components, 'type' => $type])