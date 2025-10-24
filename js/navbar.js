$(document).ready(function(){
    const token = localStorage.getItem("token");
    const loginBtn = $("#loginbtn");
    const profileBtn = $("#profile");
    const name = $("#name");
    const admin = $("#adminMenu");
    const company=$("#companyMenu");

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
                    
if(response.user.role === "user"){
    admin.hide();
    company.hide();
}
else if(response.user.role === "company"){
    company.show();
    admin.hide();
}
else if(response.user.role === "admin"){
    company.hide();
    admin.show();
}
                } else {
                    loginBtn.show();
                    profileBtn.hide();
                }
            },
            error: function() {
                loginBtn.show();
                profileBtn.hide();
            }
        });
    } else {
        loginBtn.show();
        profileBtn.hide();
    }
});

$('#logoutBtn').click(function(){
    localStorage.removeItem("token")
    window.location.href="./login.php";
})

