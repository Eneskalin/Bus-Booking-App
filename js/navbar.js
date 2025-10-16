$(document).ready(function(){
    const token = localStorage.getItem("token");
    const loginBtn = $("#loginbtn");
    const profileBtn = $("#profile");
    const name = $("#name");


    if (token) {
        $.ajax({
            url: "http://localhost:8080/auth/verify_token.php",
            method: "POST",
            headers: {
                "Authorization": "Bearer " + token
            },
            success: function(response) {
                console.log(response);
                if (response.status === "success") {
                    name.text(response.user.username);
                    loginBtn.hide();
                    profileBtn.show();
                } else {
                    loginBtn.show();
                    profileBtn.hide();
                }
            },
            error: function() {
                                console.log(response);

                loginBtn.show();
                profileBtn.hide();
            }
        });
    } else {
        // Token yoksa giriş butonu görünür
        loginBtn.show();
        profileBtn.hide();
    }
});