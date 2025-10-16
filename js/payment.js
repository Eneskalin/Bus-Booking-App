(function () {
    const notyf = new Notyf({
        duration: 3000,
        position: { x: 'right', y: 'top' },
        dismissible: true,
    });
    
    const paymentbtn = document.getElementById('payment');
    const token = localStorage.getItem("token");

    function getUrlParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    
    const value = urlParams.get(name);
    
    return value ? value : null;
}
const ticketToken = getUrlParameter('ticket');


    if (paymentbtn) {
        
        paymentbtn.addEventListener('click', async function() {
           
            if (selectedSeats.length === 0) {
                notyf.error("Lütfen en az bir koltuk seçin.");
                return;
            }

            if(selectedSeats.length != passengers){
            notyf.error(`Yolcu sayısı kadar koltuk seçebilirsiniz.`);
            return;
            }

            const url = `http://localhost:8080/handlers/payment.php?ticket=${ticketToken}`;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify(selectedSeats) 
                });

                const data = await response.json();

                if (!response.ok || data.status === 'error') {
                    const errorMessage = data.message || `İstek başarısız oldu. Durum kodu: ${response.status}`;
                    notyf.error(errorMessage);
                    console.error("Fetch Error:", data);
                    return;
                }

                notyf.success("Ödeme başarılı! Biletiniz düzenleniyor...");


            } catch (error) {
                notyf.error("Sunucuya bağlanılamadı veya bir hata oluştu.");
                console.error("Ağ Hatası:", error);
            }
        });
    }
});