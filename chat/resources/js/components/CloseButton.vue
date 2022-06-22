<template>
    <button id="request-close" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="deny"></button>
</template>
<script>
export default{
    methods: {
        deny(){
            this.$emit('deny', {
                receiver_id: this.$parent.contact_request_id
            });
        }
    }
}
</script>
