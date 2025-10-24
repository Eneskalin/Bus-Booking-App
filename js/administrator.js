        const notyf = new Notyf();

$(function(){

    const token = localStorage.getItem("token");
    const name = document.getElementById("name");
    const companyTag = document.getElementById("companies");
    const createBtn=document.getElementById("createBtn");
    const couponsTag=document.getElementById("coupons");
    const userTag=document.getElementById("users")
    const couponBtn=document.getElementById("couponBtn");
    

    $.ajax({
        url: "http://localhost:8080/handlers/management.php",
        method: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        dataType: "json",
        success: function(response){
            console.log(response);

            if(response.success === false){
                window.location.href='../forbidden.php';
            } else {
                const companies = response.companies;
                const users=response.users;
                const coupons=response.coupons; 

                if (Array.isArray(companies)) {
                    companies.forEach(company => {
                    companyTag.innerHTML += `
            <tr>
                <td width="5%"><i class="bi bi-bus-front"></i></td>
                <td>
                    <span>ID: ${company.id}</span> 
                    <span>${company.name}</span>  
                    <img class="ml-5" src="${company.logo_path}" alt="${company.name} Logo" style="height: 30px;"> 

                </td>
            </tr>
        `;
    });
}else {
    console.error("Companies array is missing or invalid in the response.");
}
if(Array.isArray(users)){
    users.forEach(user => {
        userTag.innerHTML += `
            <tr>
                <td width="5%"><i class="bi bi-person-circle"></i></td>
                <td>
                    <span>ID: ${user.id}</span> 
                    <span class="ml-5">Isim: ${user.full_name}</span>
                    <span class="ml-5">Role:${user.role}</span>
                    <span class="ml-5">Firma Id:${user.company_id}</span>  

                </td>
            </tr>
        `;
    });


} 

if(Array.isArray(coupons)){
    coupons.forEach(coupon => {
        couponsTag.innerHTML += `
            <tr>
                <td width="5%"><i class="bi bi-qr-code-scan"></i></td>
                <td>
                    <span>ID: ${coupon.id}</span> 
                    <span class="ml-5">Kod: ${coupon.code}</span>
                    <span class="ml-5">Oran(%):${coupon.discount}</span>
                    <span class="ml-5">Kullanım:${coupon.usage_limit}</span>  

                </td>
            </tr>
        `;
    });


} 


            }
        },
        error: function(xhr, status, error) {
        console.error("AJAX Hatası:", error);
        console.log("Status Code:", xhr.status);
        
        // 403 veya 401 gelirse forbidden'a yönlendir
        if(xhr.status === 403 || xhr.status === 401) {
            window.location.href = '../forbidden.php';
            return;
        }
        
        console.error("Bir hata oluştu: " + error);
    }
    });






    $('#authorizeBtn').on('click', function(e) {
        e.preventDefault();
        
        const companyId = $('#companyId').val();
        const userId = $('#user_id').val();

        

        
        if (!companyId) {
            notyf.error('Lütfen geçerli firma seçiniz.');
            return;
        }

        if (!userId) {
            notyf.error('Lütfen geçerli kullanıcı.');
            return;
        }
        

        const data = {
            action:"authorize",
            companyId: companyId,
            userId: userId,
           
        };

        $.ajax({
            url: "http://localhost:8080/handlers/management.php",
            method: "POST",
            headers: {
                "Authorization": "Bearer " + token,
                "Content-Type": "application/json"
            },
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success === true) {
                    notyf.success(`Yetki yükselitdi!`);
                    $('#companyId').val('');
                    $('#user_id').val('');
                } else {
                    notyf.error('Hata: ' + response.message);
                }
            },
            error: function() {
                notyf.error('Yetki yükseltirken hata oluştu');
            }
        });
    });
    $('#createBtn').on('click', function(e){
        e.preventDefault();
        const data = {
            action: 'create',
            name: $('#company_name').val(),
            logo_path: $('#logo_url').val(),
        };

        if (!data.name || !data.logo_path ) {
            notyf.error('Lütfen tüm alanları doldurun');
            return;
        }

        $.ajax({
            url: "http://localhost:8080/handlers/management.php",
            method: "POST",
            headers: {
                "Authorization": "Bearer " + token,
                "Content-Type": "application/json"
            },
            data: JSON.stringify(data),
            success: function(response){
                if(response.success === true) {
                    notyf.success('Firma oluşturuldu');
                    location.reload();
                } else {
                    notyf.error('Hata: ' + response.message);
                }
            },
            error: function(){
                notyf.error('Firma oluşturulurken hata oluştu');
            }
        });
    });

    $('#couponBtn').on('click', function(e){
        e.preventDefault();
        const data = {
            action: 'generate',
            code:$('#code').val(),
            expire_date: $('#couponExpireDate').val(),
            usageLimit: $('#usageLimit').val(),
            discountRate: $('#discount').val()
        };

        if (!data.expire_date || !data.usageLimit || !data.discountRate ) {
            notyf.error('Lütfen tüm alanları doldurun');
            return;
        }

        $.ajax({
            url: "http://localhost:8080/handlers/management.php",
            method: "POST",
            headers: {
                "Authorization": "Bearer " + token,
                "Content-Type": "application/json"
            },
            data: JSON.stringify(data),
            success: function(response){
                if(response.success === true) {
                    notyf.success('Kod oluşturuldu');
                    location.reload();
                } else {
                    notyf.error('Hata: ' + response.message);
                }
            },
            error: function(){
                notyf.error('Kod oluşturulurken hata oluştu');
            }
        });
    });
});







