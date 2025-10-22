        const notyf = new Notyf();

$(function(){

    const token = localStorage.getItem("token");
    const name = document.getElementById("name");
    const tripTag = document.getElementById("trips");
    const createBtn=document.getElementById("createBtn");
    const couponsTag=document.getElementById("coupons");
    

    $.ajax({
        url: "http://localhost:8080/handlers/companyAdmin.php",
        method: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        success: function(response){
            console.log(response);

            if(response.success === false){
            } else {

                name.innerText = response.username; 

                const trips = response.company_name.trips;

                if (Array.isArray(trips)) {
                    trips.forEach(trip => {
                        tripTag.innerHTML += `
                            <tr>
                                <td width="5%"><i class="bi bi-bus-front"></i></td>
                                <td>
                                    <span>${trip.id}</span>
                                    <span>${trip.departure_city}</span>
                                    <span>${trip.destination_city}</span>  <span>${trip.departure_time}</span>
                                </td>
                                <td class="level-right">
                                    <a class="button is-small is-primary" href="#" onclick="editTrip(${trip.id})" >Düzenle</a>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    console.error("Trips array is missing or invalid in the response.");
                }
            }
        }
    });


// Kuponları getir
$.ajax({
    url: "http://localhost:8080/handlers/companyCoupons.php",
    method: "POST",
    headers: {
        "Authorization": "Bearer " + token
    },
    success: function(response){
        console.log('Kupon Response:', response);

        if(response.status === "success" && response.data && response.data.coupons) {
            const coupons = response.data.coupons;

            
            if (Array.isArray(coupons)) {
                coupons.forEach(data => {
                    couponsTag.innerHTML += `
                        <tr>
                            <td width="5%"><i class="bi bi-qr-code"></i></td>
                            <td>
                                <span>ID: ${data.id}</span> | 
                                <span>Kod: <strong>${data.code}</strong></span> | 
                                <span>İndirim: %${data.discount}</span> | 
                                <span>Limit: ${data.usage_limit}</span> | 
                                <span>Son Tarih: ${data.expire_date}</span>
                            </td>
                        </tr>
                    `;
                });
            } else {
                console.error("Coupons verisi array değil!");
            }
        } else {
            console.error("Kupon verisi alınamadı:", response);
        }
    },
    error: function(xhr, status, error) {
        console.error("AJAX Hatası:", error);
        console.error("Response:", xhr.responseText);
    }
});



    $('#saveTripBtn').click(function() {
        saveTrip();
    });

    $('#editTripModal .button:not(#saveTripBtn)').click(function() {
        closeModal(document.getElementById('editTripModal'));
    });

    $(document).on('click', '#couponBtn', function(e) {
        e.preventDefault();
        
        const expireDate = $('#couponExpireDate').val();
        const discountPercentage = $('#discount').val();
        const usageLimit=$('#usageLimit').val();

        

        
        if (!expireDate) {
            notyf.error('Lütfen geçerlilik tarihini seçin.');
            return;
        }

        if (!discountPercentage) {
            notyf.error('Lütfen indirim oranını girin.');
            return;
        }
        if(!usageLimit){
            notyf.error("Lutfen limit sayisini kontrol edin");
            return;
        }

        const data = {
            expire_date: expireDate,
            discount_percentage: parseInt(discountPercentage),
            usageLimit:usageLimit
        };

        $.ajax({
            url: "http://localhost:8080/handlers/generateCoupon.php",
            method: "POST",
            headers: {
                "Authorization": "Bearer " + token,
                "Content-Type": "application/json"
            },
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success === true) {
                    notyf.success(`Kupon başarıyla oluşturuldu!\nKupon Kodu: ${response.coupon_code}\nİndirim Oranı: %${response.discount_percentage}\nGeçerlilik Tarihi: ${response.expire_date}`);
                    $('#couponExpireDate').val('');
                    $('#discount').val('');
                } else {
                    notyf.error('Hata: ' + response.message);
                }
            },
            error: function() {
                notyf.error('Kupon oluşturulurken hata oluştu');
            }
        });
    });
    $('#createBtn').on('click', function(e){
        e.preventDefault();
        const data = {
            action: 'create',
            departure_city: $('#from').val(),
            destination_city: $('#to').val(),
            price: parseFloat($('#setPrice').val()),
            departure_date: $('#setDepertureDate').val(),
            departure_time: $('#setDepertureTime').val(),
            arrival_time: $('#setArrivalTime').val(),
            capacity:27
        };

        if (!data.departure_city || !data.destination_city || !data.price || !data.departure_date || !data.departure_time || !data.arrival_time) {
            notyf.error('Lütfen tüm alanları doldurun');
            return;
        }

        $.ajax({
            url: "http://localhost:8080/handlers/companyAdmin.php",
            method: "POST",
            headers: {
                "Authorization": "Bearer " + token,
                "Content-Type": "application/json"
            },
            data: JSON.stringify(data),
            success: function(response){
                if(response.success === true) {
                    notyf.success('Sefer oluşturuldu');
                    location.reload();
                } else {
                    notyf.error('Hata: ' + response.message);
                }
            },
            error: function(){
                notyf.error('Sefer oluşturulurken hata oluştu');
            }
        });
    });
});

function editTrip(tripId) {
    const token = localStorage.getItem("token");
    
    $.ajax({
        url: "http://localhost:8080/handlers/companyAdmin.php",
        method: "POST",
        headers: {
            "Authorization": "Bearer " + token
        },
        success: function(response){
            if(response.success === true) {
                const trips = response.company_name.trips;
                const trip = trips.find(t => t.id == tripId);
                
                if (trip) {
                    $('#editTripId').val(trip.id);
                    $('#editDepartureCity').val(trip.departure_city);
                    $('#editDestinationCity').val(trip.destination_city);
                    $('#editPrice').val(trip.price);
                    
                    const departureDateTime = new Date(trip.departure_time);
                    const departureDate = departureDateTime.toISOString().split('T')[0];
                    const departureTime = departureDateTime.toTimeString().split(' ')[0].substring(0, 5);
                    
                    $('#editDepartureDate').val(departureDate);
                    $('#editDepartureTime').val(departureTime);
                    
                    const arrivalDateTime = new Date(trip.arrival_time);
                    const arrivalTime = arrivalDateTime.toTimeString().split(' ')[0].substring(0, 5);
                    
                    $('#editArrivalTime').val(arrivalTime);
                    
                    openModal(document.getElementById('editTripModal'));
                }
            }
        },
        error: function() {
            notyf.error('Sefer bilgileri alınırken hata oluştu');
        }
    });
}



function saveTrip() {
    const token = localStorage.getItem("token");
    
    const formData = {
        trip_id: $('#editTripId').val(),
        departure_city: $('#editDepartureCity').val(),
        destination_city: $('#editDestinationCity').val(),
        price: parseFloat($('#editPrice').val()),
        departure_date: $('#editDepartureDate').val(),
        departure_time: $('#editDepartureTime').val(),
        arrival_time: $('#editArrivalTime').val(),
        capacity:27
    };

    if (!formData.departure_city || !formData.destination_city || !formData.price || 
        !formData.departure_date || !formData.departure_time || !formData.arrival_time) {
        notyf.error('Lütfen tüm alanları doldurun');
        return;
    }

    const dataToSend = Object.assign({ action: 'update' }, formData);

    $.ajax({
        url: "http://localhost:8080/handlers/companyAdmin.php",
        method: "POST",
        headers: {
            "Authorization": "Bearer " + token,
            "Content-Type": "application/json"
        },
        data: JSON.stringify(dataToSend),
        success: function(response){
            if(response.success === true) {
                notyf.success('Sefer başarıyla güncellendi');
                closeModal(document.getElementById('editTripModal'));
                location.reload();
            } else {
                notyf.error('Hata: ' + response.message);
            }
        },
        error: function() {
            notyf.error('Sefer güncellenirken hata oluştu');
        }
    });
}

function openModal($el) {
    $el.classList.add('is-active');
}

function closeModal($el) {
    $el.classList.remove('is-active');
}