// click handlers when interacting with the dialog 
// received after a contact request

document.getElementById('request-close').addEventListener('click', function(){
    document.getElementById('request').style.display = 'none';
    document.getElementById('request').classList.remove('show');
});

document.getElementsByClassName('deny')[0].addEventListener('click', function(){
    document.getElementById('request').style.display = 'none';
    document.getElementById('request').classList.remove('show');
});

document.getElementsByClassName('accept')[0].addEventListener('click', function(){
    document.getElementById('request').style.display = 'none';
    document.getElementById('request').classList.remove('show');
});
