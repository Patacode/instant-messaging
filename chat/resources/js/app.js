// node modules
require('./bootstrap');
window.Vue = require('vue').default;
window.CryptoJS = require('crypto-js');

// Vue components 
Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('chat-messages', require('./components/ChatMessages.vue').default);
Vue.component('chat-form', require('./components/ChatForm.vue').default);
Vue.component('contact-cards', require('./components/ContactCards.vue').default);
Vue.component('user-cards', require('./components/UserCards.vue').default);
Vue.component('accept-btn', require('./components/AcceptButton.vue').default);
Vue.component('deny-btn', require('./components/DenyButton.vue').default);
Vue.component('close-btn', require('./components/CloseButton.vue').default);

// Vue instance for the web pages
const app = new Vue({
    el: '#app',
    data: {
        current_user: {}, // current user informations
        contacts: [], // contacts of current user
        users: [], // all the website's user
        contact_request_id: -1 // id of current contact request
    },
    created() {
        this.fetchCurrentUserData();
        this.fetchCurrentUserContacts();
        this.fetchAllUsers();
        this.sendKeypair(2048);

        // channel to chat
        window.Echo.private('encrypted-user_chat')
            .listen('MessageSent', (e) => { // received when a message is sent
                // create message client side for both user (message will be created on reicever side only if receiver is on chat page)
                let message = JSON.parse(e.message);
                if(e.receiver.id == this.current_user.id){
                    let pbkey = "";
                    for(let contact of this.contacts){
                        if(contact.id == e.user.id){
                            pbkey = contact.publicKey;
                            break;
                        }
                    }

                    let result = verif(message.message, message.signature, pbkey);
                    if(result && onChatPage(e.user.id)){
                        createMessage(e.user, message); // from util.js
                    } else if(result && !onChatPage(e.user.id)){
                        displaySuccessMessage(`User ${e.user.name} sent you a message !`);
                    } else{
                        this.deleteMessage(message.id);
                        displayDangerMessage(`Invalid message received. Identity of sender cannot be ensured. Try to reload the page.`);
                    }
                } else if(e.user.id == this.current_user.id){
                    createMessage(e.user, message); // from util.js
                }
            });

        // main channel where user land after authenticating
        window.Echo.private('encrypted-user_room')
            .listen('UserConnected', (e) => { // received when a user log in
                this.fetchAllUsers();
                if(contactExists(this.contacts, e.user.id)){
                    displaySuccessMessage(`User ${e.user.name} is now connected`);
                }
            }).listen('UserDisconnected', (e) => { // received when a user log out
                if(contactExists(this.contacts, e.user.id)){ 
                    displayDangerMessage(`User ${e.user.name} is now disconnected`);
                }
            }).listen('ContactRequested', (e) => { // received when a contact request has been sended
                if(e.receiver_id == this.current_user.id){
                    document.getElementById('request').classList.add('show');
                    document.getElementById('request').style.display = 'block';
                    document.getElementById('request-content').innerText = `User ${e.sender.name} would like to add you in his friend list.`;
                    this.contact_request_id = e.sender.id;
                }
            }).listen('ResponseAccepted', (e) => { // received when contact request has been accepted
                if(e.receiver_id == this.current_user.id || e.sender.id == this.current_user.id){
                    this.fetchCurrentUserContacts();
                    displaySuccessMessage(`You are now both friends`);
                }
            }).listen('ResponseDenied', (e) => { // received when contact request has been denied
                if(e.receiver_id == this.current_user.id){
                    displayDangerMessage('Contact request denied');
                }
            }).listen('ContactRemoved', (e) => { // received when a user removes a contact
                if(e.receiver_id == this.current_user.id || e.sender.id == this.current_user.id){
                    this.fetchCurrentUserContacts();
                    if(e.receiver_id == this.current_user.id){
                        displayDangerMessage(`User ${e.sender.name} removed you from his contacts`);
                    }
                }
            }).error((error) => console.log('An error occured'));
    },
    methods: {

        // === Every routes require current user to be authenticated ===
        // (Because each route is linked to a controller method which requires authentication to be called)

        /**
         * Fetches current user informations.
         */
        fetchCurrentUserData(){
            axios.get('/user/info').then(response => {
                let data = response.data;
                this.current_user = {id : data.id, name: data.name, email: data.email};
            }).catch(err => console.log("Not authenticated"));
        },

        /**
         * Fetches current user contacts.
         */
        fetchCurrentUserContacts(){
            axios.get('/user/contacts').then(response => {
                this.contacts = [];
                for(let contact of response.data){
                    this.contacts.push({
                        id: contact.id,
                        name: contact.name,
                        email: contact.email,
                        publicKey: contact.public_key,
                    });
                }
            }).catch(err => console.log("Not authenticated"));
        },

        /**
         * Fetches all the users recorded in database.
         */
        fetchAllUsers(){
            axios.get('/user/all').then(response => {
                this.users = [];
                for(let user of response.data){
                    this.users.push({
                        id: user.id,
                        name: user.name,
                        email: user.email
                    });
                }
            }).catch(err => console.log('Not authenticated'));
        },

        /**
         * Adds a message to database by sending post request.
         *
         * @param message the message to be sent
         */
        addMessage(message){
            let signature = sign(message.message, sessionStorage.getItem('privateKey'));
            message.signature = signature;
            axios.post('/pchat/add', message).then(response => {
                if(response.data.success)
                    displaySuccessMessage(response.data.success)
                else if(response.data.error)
                    displayDangerMessage(response.data.error)
            });
        },

        deleteMessage(id){
            axios.post('/pchat/delete', {id: id}).then(response => {
                if(response.data.error)
                    displayDangerMessage(response.data.error)
            });
        },

        /**
         * Sends a contact request to the user denoted by the given id. The current user
         * will be warned and the request will not operate if he tries to send a contact request
         * to a user who's already in his contact list.
         *
         * @param receiver the object containing the id of contact request's receiver
         */
        sendContactRequest(receiver){
            if(contactExists(this.contacts, receiver.receiver_id)){
                displayDangerMessage('This user is already in your contact list');
            } else if(receiver.receiver_id == this.current_user.id){
                displayDangerMessage('You cannot add yourself to your contact list');
            } else{
                axios.post('/puser/request', receiver).then(response => {
                    if(response.data.success)
                        displaySuccessMessage(response.data.success)
                    else if(response.data.error)
                        displayDangerMessage(response.data.error)
                });
            }
        },

        /**
         * Sends a deny response to the most recent received contact request to the user denoted by the given id.
         *
         * @param receiver the object containing the id of contact response's receiver
         */
        sendDeny(receiver){
            axios.post('/puser/response/false', receiver).then(response => {
                if(response.data.success)
                    displaySuccessMessage(response.data.success)
                else if(response.data.error)
                    displayDangerMessage(response.data.error)
            });
        },

        /**
         * Sends a success response to the most recent received contact request to the user denoted by the given id.
         *
         * @param receiver the object containing the id of contact response's receiver
         */
        sendAccept(receiver){
            axios.post('/puser/response/true', receiver).then(response => {
                if(response.data.success)
                    displaySuccessMessage(response.data.success);
                else if(response.data.error)
                    displayDangerMessage(response.data.error);
            });
        },

        /**
         * Removes the user denoted by the given id from the current user's contact list
         *
         * @param receiver the object containing the id of the contact to remove
         */
        sendRemoveContact(receiver){
            if(!contactExists(this.contacts, receiver.receiver_id)){
                displayDangerMessage("This user is not your contact list, can't remove it");
            } else{
                axios.post('/puser/remove', receiver).then(response => {
                    if(response.data.success)
                        displaySuccessMessage(response.data.success)
                    else if(response.data.error)
                        displayDangerMessage(response.data.error)
                });
            }
        },

        /**
         * Generates RSA keypair for the client and store it in session storage and sends public key 
         * to server (see public/js/util.js#generateKeypair(int)).
         *
         * @param size the default key size
         */
        sendKeypair(size){
            generateKeypair(size);
            axios.post('/puser/key', {publicKey: sessionStorage.getItem('publicKey')}).then(response => {
                console.log(response.data);
            });
        },

    }
});

