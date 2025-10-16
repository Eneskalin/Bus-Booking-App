

$(function(){ 
    
    const notyf = new Notyf({
        duration: 3000,
        position: { x: 'right', y: 'top' },
        dismissible: true,
    });

    const form = $('form[action="search.php"]');
    const fromSelect = $('#from');
    const toSelect = $('#to');
    const dateInput = $('input[name="date"]');

    form.on('submit', function(event) {
        const fromValue = fromSelect.val();
        const toValue = toSelect.val();

        if (fromValue === toValue && fromValue !== "") {
            notyf.error('Kalkış yeri ve varış yeri aynı olamaz! Lütfen farklı yerler seçin.');
            event.preventDefault(); 
            return false;
        }

        const selectedDate = new Date(dateInput.val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (selectedDate < today) {
            notyf.error('Geçmiş bir tarih seçemezsiniz! Lütfen bugünü veya ileri bir tarihi seçin.');
            event.preventDefault(); 
            return false;
        }

        return true;
    });
});