$(function () {
        const notyf = new Notyf({
        duration: 3000,
        position: { x: 'right', y: 'top' },
        dismissible: true,
    });
    const svgRoot = document.querySelector('#bus_svg svg');
    const maxSelectable = typeof passengers !== 'undefined' ? passengers : 1; 

    if (!svgRoot) return; 

    const originalFills = new Map();
    



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

    // 1. Koltuk Seçim Mantığı
    if (selectedSeats.includes(seatNumber)) {
        selectedSeats = selectedSeats.filter(s => s !== seatNumber);
        
        updateSeatColors(g, original.body, original.number); 
        g.classList.remove('seat-selected');
    } else {
        if (selectedSeats.length >= maxSelectable) {
            notyf.error(`Yolcu sayısı kadar koltuk seçebilirsiniz.`);
            return;
        }
        selectedSeats.push(seatNumber);
        g.classList.add('seat-selected');
        
        updateSeatColors(g, '#faad1a', '#ffffff'); 
    }

    
    const displayElement = document.getElementById('selectedSeatsDisplay'); 
    if (displayElement) {
        if (selectedSeats.length > 0) {
            displayElement.textContent = selectedSeats.join(', ');
        } else {
            displayElement.textContent = "--"; 
        }
    }

    const seatLabel = document.getElementById('selected-seat'); 
    if (seatLabel) {
        seatLabel.textContent = selectedSeats.join(', ');
    } 
    
    const seatInput = document.getElementById('selected-seat-input');
    if (seatInput) {
        seatInput.value = selectedSeats.join(',');
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