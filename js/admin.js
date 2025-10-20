$(function(){
    const token = localStorage.getItem("token");
    const name = document.getElementById("name");
    const tripTag = document.getElementById("trips");
    const createBtn=document.getElementById("createBtn");

    

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

    $('#saveTripBtn').click(function() {
        saveTrip();
    });

    $('#editTripModal .button:not(#saveTripBtn)').click(function() {
        closeModal(document.getElementById('editTripModal'));
    });
    // Create trip
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
            alert('Lütfen tüm alanları doldurun');
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
                    alert('Sefer oluşturuldu');
                    location.reload();
                } else {
                    alert('Hata: ' + response.message);
                }
            },
            error: function(){
                alert('Sefer oluşturulurken hata oluştu');
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
            alert('Sefer bilgileri alınırken hata oluştu');
        }
    });
}


// removed broken createBtn.click block; replaced with #createBtn handler above

function saveTrip() {
    const token = localStorage.getItem("token");
    
    const formData = {
        trip_id: $('#editTripId').val(),
        departure_city: $('#editDepartureCity').val(),
        destination_city: $('#editDestinationCity').val(),
        price: parseFloat($('#editPrice').val()),
        departure_date: $('#editDepartureDate').val(),
        departure_time: $('#editDepartureTime').val(),
        arrival_time: $('#editArrivalTime').val()
    };

    if (!formData.departure_city || !formData.destination_city || !formData.price || 
        !formData.departure_date || !formData.departure_time || !formData.arrival_time) {
        alert('Lütfen tüm alanları doldurun');
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
                alert('Sefer başarıyla güncellendi');
                closeModal(document.getElementById('editTripModal'));
                location.reload();
            } else {
                alert('Hata: ' + response.message);
            }
        },
        error: function() {
            alert('Sefer güncellenirken hata oluştu');
        }
    });
}

function openModal($el) {
    $el.classList.add('is-active');
}

function closeModal($el) {
    $el.classList.remove('is-active');
}