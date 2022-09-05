var contactForm = document.querySelector("form");
var nameInput = document.querySelector("#name");
var organizatioNameInput = document.querySelector("#organizationName");
var mobileNoInput = document.querySelector("#mobileNo");
var emailAddressInput = document.querySelector("#emailAddress");
var captchaInput = document.querySelector("#captchaInput");
var captcha = document.querySelector("#captcha");
var comment = document.querySelector("#comment");

if (contactForm) {
  contactForm.onsubmit = (e) => {
    e.preventDefault();
    var interest = document.querySelector(
      'input[type="radio"][name="interest"]:checked'
    );
    console.log(nameInput?.value);
    console.log(organizatioNameInput?.value);
    console.log(mobileNoInput?.value);
    console.log(emailAddressInput?.value);
    console.log(interest?.value);
    console.log(captchaInput?.value);
    console.log(comment?.value);
    //   POST on form submit
  };
}

var captchaResetButton = document.querySelector("#resetCaptcha");
captchaResetButton.addEventListener("click", (e) => {
  captchaInput.value = "";
});
