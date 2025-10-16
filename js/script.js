$(function () {
    const notyf = new Notyf();
    const loginText = document.querySelector(".title-text .login");
    const loginFormElement = document.querySelector("form.login");
    const loginFormJQuery = $("form.login");
    const loginBtn = document.querySelector("label.login");
    const signupBtn = document.querySelector("label.signup");
    const signupLink = document.querySelector("form .signup-link a");
    
    // Anımasyonlar
    signupBtn && (signupBtn.onclick = (() => {
        if (loginFormElement && loginText) {
            loginFormElement.style.marginLeft = "-50%";
            loginText.style.marginLeft = "-50%";
        }
    }));

    loginBtn && (loginBtn.onclick = (() => {
        if (loginFormElement && loginText) {
            loginFormElement.style.marginLeft = "0%";
            loginText.style.marginLeft = "0%";
        }
    }));

    signupLink && (signupLink.onclick = ((e) => {
        e.preventDefault();
        if (signupBtn) signupBtn.click();
    }));

    // Doğrulama
    $("form.login, form.signup").on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const action = form.find("input[name='action']").val();

        if (action === 'login') {
            const email = $.trim(form.find("input[name='email']").val());
            const password = $.trim(form.find("input[name='password']").val());
            if (!email || !password) {
                notyf.error('Email veya parola boş olamaz');
                return;
            }
        } else if (action === 'signup') {
            const full_name = $.trim(form.find("input[name='full_name']").val());
            const email = $.trim(form.find("input[name='email']").val());
            const password = $.trim(form.find("input[name='password']").val());
            const password_confirm = $.trim(form.find("input[name='password_confirm']").val());
            if (!full_name || !email || !password || !password_confirm) {
                notyf.error('Lütfen tüm alanları doldurun.');
                return;
            }
            if (password !== password_confirm) {
                notyf.error('Şifreler eşleşmiyor.');
                return;
            }
        }

        const postData = form.serialize();

        $.ajax({
            url: "http://localhost:8080/auth/auth.php",
            method: "POST",
            data: postData,
            dataType: "json",
            success: function (response) {
                if (!response) {
                    notyf.error('Sunucudan beklenmeyen yanıt alındı.');
                    return;
                }
                
                if (response.status === 'success') {
                    
                    notyf.success(response.message || 'İşlem başarılı');
                    localStorage.setItem("token",response.token);
                    if (response.redirect) {
                        const delayMs = (response.delay || 1) * 1000;
                        setTimeout(function () {
                            window.location.href = response.redirect;
                        }, delayMs);
                    }
                } else {
                    notyf.error(response.message || 'İşlem başarısız');
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Hatası:", status, error, xhr.responseText);
                notyf.error('Sunucuya bağlanırken bir hata oluştu veya giriş başarısız oldu.' +error);
            }
        });
    });

    const buyButtons = document.querySelectorAll('.buy-ticket-btn');
  
    // Bilet bilgileri
buyButtons.forEach(function (button, index) {
    button.addEventListener('click', function (e) {
        e.preventDefault();
        
        const token = localStorage.getItem("token");
        
        if (!token) {
            notyf.error("Lütfen önce giriş yapın.");
            return;
        }
        
        const tripId = this.getAttribute('data-trip-id');
        const totalPrice = this.getAttribute('data-total-price');
        const passengers = this.getAttribute('data-passengers');
        
        const requestData = {
            tripId: tripId,
            totalPrice: totalPrice,
            passengers: passengers
        };
        console.log(requestData);
        
        button.disabled = true;
        button.classList.add('is-loading');
        
        fetch("http://localhost:8080/handlers/ticket.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": "Bearer " + token
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("JSON Parse Hatası:", e);
                    throw new Error("Sunucu geçersiz yanıt döndürdü");
                }
            });
        })
        .then(data => {
            console.log("✅ Sunucu yanıtı:", data);
            
            // Token hatası
            if (data.status === 'error') {
                notyf.error(data.message || "Bir hata oluştu!");
                

                
                button.disabled = false;
                button.classList.remove('is-loading');
            }
            // Başarılı
            else if (data.success === true) {
                notyf.success(data.message || "Bilet başarıyla oluşturuldu!");
                
                
                // Yönlendirme
                if (data.redirect_url) {
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1000); // 1 saniye bekle ki kullanıcı bildirimi görsün
                } else {
                    button.disabled = false;
                    button.classList.remove('is-loading');
                }
            }
            // Başarısız
            else if (data.success === false) {
                notyf.error(data.message || "Bir hata oluştu.");
                button.disabled = false;
                button.classList.remove('is-loading');
            }
            // Bilinmeyen format
            else {
                console.warn("⚠️ Bilinmeyen yanıt formatı:", data);
                notyf.error("Beklenmeyen yanıt alındı.");
                button.disabled = false;
                button.classList.remove('is-loading');
            }
        })
        .catch(error => {
            console.error("❌ Fetch Hatası:", error);
            notyf.error("Sunucuya bağlanırken bir hata oluştu: " + error.message);
            button.disabled = false;
            button.classList.remove('is-loading');
        });
    });
});





});

