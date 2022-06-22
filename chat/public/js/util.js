/**
 * Checks if contact denoted by the given id is in the given contacts.
 *
 * @param contacts an array of contact
 * @param id the id of the searched contact
 *
 * @return true if a matching contact is found, false otherwise.
 */
function contactExists(contacts, id){
    for(let contact of contacts){
        if(contact.id == id) return true;
    }

    return false;
}

/**
 * Displays the given message as a danger.
 *
 * @param message_content the message to be displayed
 */
function displayDangerMessage(message_content){
    document.getElementById('notif').classList.remove('hide');
    document.getElementById('notif').classList.add('show');
    document.getElementById('notif').classList.remove('bg-success');
    document.getElementById('notif').classList.add('bg-danger');
    document.getElementById('notif-content').innerText = message_content;
}

/**
 * Displays the given message as a success.
 *
 * @param message_content the message to be displayed
 */
function displaySuccessMessage(message_content){
    document.getElementById('notif').classList.remove('hide');
    document.getElementById('notif').classList.add('show');
    document.getElementById('notif').classList.remove('bg-danger');
    document.getElementById('notif').classList.add('bg-success');
    document.getElementById('notif-content').innerText = message_content;
}

/**
 * Creates a messages using the given user and message information.
 *
 * @param user the user who sent the message
 * @param message the message sent by the user
 */
function createMessage(user, message){
    let container = document.querySelector('.card-body');

    let ul = document.createElement('ul');
    let li = document.createElement('li');
    let clrfix = document.createElement('div');
    let header = document.createElement('div');
    let author = document.createElement('strong');
    let email = document.createElement('em');
    let content = document.createElement('p');

    let msg_content = document.createTextNode(message.message);
    let msg_author = document.createTextNode(user.name + " ");
    let msg_email = document.createTextNode(user.email);

    ul.classList.add('chat');
    li.classList.add('left', 'clearfix');
    clrfix.classList.add('clearfix');
    header.classList.add('header');
    content.setAttribute('id', message.id);

    author.appendChild(msg_author);
    email.appendChild(msg_email);
    content.appendChild(msg_content);

    header.appendChild(author);
    header.appendChild(email);

    clrfix.appendChild(header);
    clrfix.appendChild(content);

    li.appendChild(clrfix);
    ul.appendChild(li);

    container.appendChild(ul);
}

/**
 * Formats the given string date as "year:month:day hour:min:sec".
 *
 * @param date_str the string date to format
 *
 * @return the string date properly formatted.
 */
function format_date(date_str){
    let date = new Date(date_str);

    let y = String(date.getFullYear()).padStart(4, '0');
    let M = String(date.getMonth()).padStart(2, '0');
    let d = String(date.getDate()).padStart(2, '0');

    let h = String(date.getHours()).padStart(2, '0');
    let m = String(date.getMinutes()).padStart(2, '0');
    let s = String(date.getSeconds()).padStart(2, '0');

    return `${y}-${M}-${d} ${h}:${m}:${s}`;
}

/**
 * Checks if the current user is on a chat page talking with the contact denoted by the given id.
 *
 * @param contact_id the id of the contact with whom current user is talking
 *
 * @return true if the current user is on the right chat page, false otherwise.
 */
function onChatPage(contact_id){
    return document.querySelector(`#contact-${contact_id}`) != null;
}


/**
 * Generate a public and private RSA keys of given size and place them in session storage if they are not already
 * present.
 *
 * @param size the key size
 *
 * @return true if keypair is generated, false otherwise.
 */
function generateKeypair(size){
    if(!sessionStorage.getItem('publicKey') && !sessionStorage.getItem('privateKey')){
        jse = new JSEncrypt({default_key_size: size});
        jse.getKey();
        sessionStorage.setItem('publicKey', jse.getPublicKey());
        sessionStorage.setItem('privateKey', jse.getPrivateKey());

        return true;
    }

    return false;
}

/**
 * Signs a message using the given private key with SHA 256.
 *
 * @param message the message to be signed
 * @param pvkey the private key to be used
 * 
 * @return the message signature.
 */
function sign(message, pvkey){
    let jse = new JSEncrypt();
    jse.setPrivateKey(pvkey);
    return jse.sign(message, window.CryptoJS.SHA256, "sha256");
}

/**
 * Verify with the given public key that the message has been signed with signature.
 *
 * @param message the message to be verified
 * @param signature the message signature
 * @param pbkey the public key to be used
 *
 * @return true if the message has been signed with signature, false otherwise.
 */
function verif(message, signature, pbkey){
    let jse = new JSEncrypt();
    jse.setPublicKey(pbkey);
    return jse.verify(message, signature, CryptoJS.SHA256);
}

