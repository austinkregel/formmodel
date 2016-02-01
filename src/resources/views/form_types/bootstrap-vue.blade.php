<div id="vue-form-wrapper">
    <div id ="response" v-show="response">
        @{{ response }}
        <div class="close" @click="close">&times;</div>
    </div>
    {!! $form_ !!}
</div>
@include('formmodel::vue', ['vue_components' => $components, 'type' => $type])