let loginbox = document.querySelector('.login-box'); 
let registerbox = document.querySelector('.register-box'); 
let logintab = document.querySelector('.login-tab'); 
let registertab = document.querySelector('.register-tab');
let signin = document.querySelector('.signin');
let signup = document.querySelector('.signup');


registertab.addEventListener('click',function(){
    x();
});

logintab.addEventListener('click',function(){
   y();
});

signup.addEventListener('click',function(){
   x();
});

signin.addEventListener('click',function(){
   y();
});

function x(){
    loginbox.style.display = "none";
    registerbox.style.display = "block";
    registertab.classList.add('active');
    logintab.classList.remove('active');
}

function y(){
    loginbox.style.display = "block";
    registerbox.style.display = "none";
    logintab.classList.add('active');
    registertab.classList.remove('active');
}
