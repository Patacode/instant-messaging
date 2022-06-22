<template>
    <section id="users" class="card p-3">
        <h2>Users</h2>
        <div class="card mb-2" v-for="user in users" :key="user.id">
            <div class="card-header d-flex justify-content-between">
                <div class="fw-bold fs-4 d-flex flex-column">
                    <div class="me-1">{{ user.name }}</div>
                    <div class="me-1"><em>{{ user.email }}</em></div>
                </div>
                <button :id="user.id" style="height:fit-content;" @click="sendContactRequest">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
        </div>
    </section>
</template>
<script>
export default{
    props: ['users'],
    methods: {
        sendContactRequest(event){
            let contact_id = 0;
            if(event.target.localName == 'i'){
                contact_id = event.target.parentNode.id;
            } else contact_id = event.target.id;

            this.$emit("contactrequest", {
                receiver_id: contact_id
            });
        }
    }
}
</script>

