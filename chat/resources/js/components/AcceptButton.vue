<template>
    <button type="button" class="btn btn-success accept" @click="accept">Accept</button>
</template>
<script>
export default{
    methods: {
        accept(){
            this.$emit('accept', {
                receiver_id: this.$parent.contact_request_id
            });
        }
    }
}
</script>
