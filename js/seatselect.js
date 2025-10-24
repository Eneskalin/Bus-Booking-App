$(function () {
    const notyf = new Notyf({
        duration: 3000,
        position: { x: 'right', y: 'top' },
        dismissible: true,
    });
        localStorage.removeItem("discount");

    const svgRoot = document.querySelector('#bus_svg svg');
    const maxSelectable = typeof passengers !== 'undefined' ? passengers : 1;
    const discountBtn = document.getElementById('discountBtn');

    const token = localStorage.getItem("token");
    if (!svgRoot) return;

    const originalFills = new Map();

    async function submitCoupon() {
        const code = $("#couponbar").val().trim();
        if (code === "") {
            notyf.error("Kupon boş olamaz");
            return;
        }

        // URL'den ticket token'ını al
        const urlParams = new URLSearchParams(window.location.search);
        const ticketToken = urlParams.get('ticket');
        
        if (!ticketToken) {
            notyf.error("Bilet bilgisi bulunamadı");
            return;
        }

        const url = "http://localhost:8080/handlers/submitCoupon.php";

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    "Authorization": `Bearer ${token}`,
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    coupon_code: code,
                    ticket_token: ticketToken
                })
            });

            if (!response.ok) {
                let errorData;
                try {
                    errorData = await response.json();
                    notyf.error(errorData.message || `Kupon kontrol edilirken sunucu hatası oluştu: HTTP ${response.status}`);
                } catch (e) {
                    notyf.error(`Kupon kontrol edilirken bilinmeyen bir hata oluştu: HTTP ${response.status}`);
                }
                return;
            }

            const data = await response.json();

            if (data.status === 'success') {
                
                notyf.success(`Kupon uygulandı!`);
                


                localStorage.setItem("discount", data.token); 
                
                if (data.new_price) {
                    $('.result').html(`
                        Kredi: ${data.balance} ₺<br>
                        Eski Tutar: <s>${data.original_price} ₺</s><br>
                        İndirim: %${data.discount}<br>
                        <strong>Yeni Tutar: ${data.new_price} ₺</strong>
                    `);
                }
                $("#couponbar").prop('disabled', true);
                $("#discountBtn").prop('disabled', true).text('Uygulandı');

            } else {
                // PHP kodundan gelen status: 'error' durumu
                notyf.error(data.message || 'Kupon uygulanamadı');
            }

        } catch (error) {
            console.error('Fetch Hatası:', error);
            notyf.error('Kupon kontrol edilirken bir ağ hatası oluştu');
        }
    }
    
discountBtn.addEventListener('click', () => submitCoupon());



    window.selectedSeats = []; 


    function setElementFill(element, color) {
        if (element) {
            element.setAttribute('fill', color);
        }
    }

    function updateSeatColors(g, bodyColor, numberColor) {
        const seatBodies = g.querySelectorAll('path:not(.seatnumber)');
        const seatNumberPath = g.querySelector('.seatnumber');

        seatBodies.forEach(path => setElementFill(path, bodyColor));

        setElementFill(seatNumberPath, numberColor);
    }

    function toggleSeatSelection(g) {
        const seatNumber = parseInt(g.id.replace('seat', ''), 10);
        const original = originalFills.get(g.id);

        if (window.selectedSeats.includes(seatNumber)) {
            window.selectedSeats = window.selectedSeats.filter(s => s !== seatNumber);

            updateSeatColors(g, original.body, original.number);
            g.classList.remove('seat-selected');
        } else {
            if (window.selectedSeats.length >= maxSelectable) {
                notyf.error(`Yolcu sayısı kadar koltuk seçebilirsiniz.`);
                return;
            }
            window.selectedSeats.push(seatNumber);
            g.classList.add('seat-selected');

            updateSeatColors(g, '#faad1a', '#ffffff');
        }


        const displayElement = document.getElementById('selectedSeatsDisplay');
        if (displayElement) {
            if (window.selectedSeats.length > 0) {
                displayElement.textContent = window.selectedSeats.join(', ');
            } else {
                displayElement.textContent = "--";
            }
        }

        const seatLabel = document.getElementById('selected-seat');
        if (seatLabel) {
            seatLabel.textContent = window.selectedSeats.join(', ');
        }

        const seatInput = document.getElementById('selected-seat-input');
        if (seatInput) {
            seatInput.value = window.selectedSeats.join(',');
        }
    }


    const seatGroups = Array.from(svgRoot.querySelectorAll('g')).filter(g => /^seat\d+/i.test(g.id));

    seatGroups.forEach(g => {

        const seatBodies = g.querySelectorAll('path:not(.seatnumber)');
        const seatNumberPath = g.querySelector('.seatnumber');

        const firstSeatBody = seatBodies[0];
        const origBody = firstSeatBody ? firstSeatBody.getAttribute('fill') || '#A7A7A7' : '#A7A7A7';
        const origNumber = seatNumberPath ? seatNumberPath.getAttribute('fill') || 'black' : 'black';

        originalFills.set(g.id, { body: origBody, number: origNumber });

        g.style.cursor = 'pointer';

        const seatNumber = parseInt(g.id.replace('seat', ''), 10);
        
        if (typeof bookedSeats !== 'undefined' && bookedSeats.includes(seatNumber)) {
            updateSeatColors(g, '#ff4d4d', '#ffffff');

            g.classList.add('seat-booked');
            g.style.pointerEvents = 'none';
            return;
        }

        g.addEventListener('click', (ev) => {

            ev.stopPropagation();
            toggleSeatSelection(g);
        });
    });
});