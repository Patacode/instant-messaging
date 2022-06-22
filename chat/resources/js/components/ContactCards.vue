<template>
    <section id="contacts" class="card p-3 mb-3">
        <h2>Contacts</h2>
        <div class="card mb-2" v-for="contact in contacts" :key="contact.id">
            <div class="card-header d-flex justify-content-between">
                <div class="fw-bold fs-4 d-flex flex-column">
                    <div class="me-1">{{ contact.name }}</div>
                    <div class="me-1"><em>{{ contact.email }}</em></div>
                </div>
                <button :id="contact.id" style="height:fit-content;" @click="sendRemoveContact">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="card-body">
                <a :href="`/chat/init/${contact.id}`" class="btn btn-primary">Start chatting</a>
            </div>
        </div>
    </section>
</template>
<script>
export default{
    props: ["contacts"],
    methods: {
        sendRemoveContact(event){
            let contact_id = 0;
            if(event.target.localName == 'i'){
                contact_id = event.target.parentNode.id;
            } else contact_id = event.target.id;
            this.$emit("contactdelete", {
                receiver_id: contact_id
            });
        }
    },
}
</script>
