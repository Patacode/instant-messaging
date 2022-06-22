<template>
    <button type="button" class="btn btn-danger deny" data-bs-dismiss="modal" @click="deny">Deny</button>
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
