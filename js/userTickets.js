const notyf = new Notyf();
$(function () {
    const ticketContainer = document.querySelector('#ticketList');
    const token = localStorage.getItem("token");

    fetch("http://localhost:8080/handlers/userTickets.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token
        },
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

            if (data.status === 'error') {
                localStorage.removeItem("token"); 
                window.location.href = '../forbidden.php';
            }
            else if (data.success === true) {
                ticketContainer.innerHTML = '';

                if (data.tickets && data.tickets.length > 0) {
                    data.tickets.forEach(ticket => {
                        ticketContainer.innerHTML += `
                        <div class="columns">
                            <div class="column is-9">
                                <div class="trip-results">
                                    <div class="trip-card has-background-light">
                                        <div class="columns is-vcentered">
                                            <div class="column is-2">
                                                <div class="company-logo">
                                                    <img src="${ticket.logo_path}" 
                                                         alt="Şirket Logo"
                                                         style="max-width: 80px;">
                                                    <p class="company-name has-text-primary">${ticket.name}</p>
                                                   
                                                </div>
                                            </div>
                                            <div class="column is-3">
                                                <div class="trip-info">
                                                    <h3 class="departure-city has-text-link">${ticket.departure_city}</h3>
                                                    <p class="departure-time has-text-link is-size-4">${new Date(ticket.departure_time).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })}</p>
                                                    <h3 class="has-text-grey is-size-7">${new Date(ticket.departure_time).toLocaleDateString('tr-TR')}</h3>
                                                </div>
                                            </div>
                                            <div class="column is-2">
                                                <div class="duration-info has-text-centered">
                                                    <i class="bi bi-arrow-right has-text-primary is-size-5"></i>
                                                    <p class="has-text-grey is-size-7 mt-2">Sefer #${ticket.trip_id}</p>
                                                    <div class="route-line"></div>
                                                </div>
                                            </div>
                                            <div class="column is-3">
                                                <div class="trip-info">
                                                    <h3 class="arrival-city has-text-link is-size-5">${ticket.destination_city}</h3>
                                                    <p class="has-text-grey is-size-7">Varış Noktası</p>
                                                </div>
                                            </div>
                                            <div class="column is-2">
                                                <div class="price-info">
<p class="price has-text-dark is-size-4"><strong style="color: #000000;">${ticket.total_price}₺</strong></p>
                                                    <p class="price-per-person has-text-primary">Toplam Fiyat</p>
                                                    <button class="button is-danger is-fullwidth mt-2" 
                                                            onclick="cancelTicket(${ticket.id})">
                                                        <i class="bi bi-x-circle mr-2"></i>
                                                        İptal Et
                                                    </button>
                                                    <button class="button is-link is-fullwidth mt-2" 
                                                            onclick="downloadTicket(${ticket.id})">
                                                        <i class="bi bi-download mr-2"></i>
                                                        İndir
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    });
                } else {
                    ticketContainer.innerHTML = '<div class="notification is-info">Henüz biletiniz bulunmamaktadır.</div>';
                }
            }
            else if (data.success === false) {
                ticketContainer.innerHTML = '<div class="notification is-warning">' + (data.message || 'İşlem başarısız oldu') + '</div>';
            }
            else {
                ticketContainer.innerHTML = '<div class="notification is-danger">Beklenmeyen bir yanıt alındı.</div>';
            }
        })
        .catch(error => {
            console.error("❌ Fetch Hatası:", error);
            ticketContainer.innerHTML = '<div class="notification is-danger">Bağlantı hatası: ' + error.message + '</div>';
        });
});

function cancelTicket(ticketId) {
    if (!confirm('Bu bileti iptal etmek istediğinize emin misiniz?')) {
        return;
    }

    const token = localStorage.getItem("token");

    fetch("http://localhost:8080/handlers/cancelTicket.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token
        },
        body: JSON.stringify({ ticket_id: ticketId })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notyf.success('Bilet başarıyla iptal edildi.');
                location.reload();
            } else {
                notyf.error('Bilet iptal edilemedi: ' + (data.message || 'Bilinmeyen hata'));
            }
        })
        .catch(error => {
            console.error('İptal hatası:', error);
            notyf.error('Bir hata oluştu.');
        });
}

function downloadTicket(ticketId) {
    const token = localStorage.getItem("token");


    fetch("http://localhost:8080/handlers/downloadTicket.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token
        },
        body: JSON.stringify({ ticket: ticketId })
    })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.message || 'Bilet indirilirken sunucu hatası oluştu.');
                }).catch(() => {
                    throw new Error('Bilet indirilirken bilinmeyen bir ağ/sunucu hatası oluştu.');
                });
            }

            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;

            a.download = 'bilet_' + ticketId + '.pdf';

            document.body.appendChild(a);
            a.click();

            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

            console.log("✅ Bilet indirme işlemi başladı.");
        })
        .catch(error => {
            console.error("❌ İndirme Hatası:", error);
            notyf.error(error.message);
        });
}




