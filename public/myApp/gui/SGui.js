class SGui {
    constructor() {}

    static showWaiting(iTimer = null) {
        if (iTimer != null) {
            Swal.fire({
                title: 'Espera...',
                timer: iTimer,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }else{
            Swal.fire({
                title: 'Espera...',
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    }

    static showWaitingBlock(iTimer) {
        Swal.fire({
            title: 'Espera...',
            timer: iTimer,
            timerProgressBar: true,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    static showOk() {
        Swal.fire({
            title: '¡Realizado!',
            timer: 1500,
            icon: 'success'
        });
    }

    static showError(sError) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: sError,
        });
    }

    static showMessage(sTitle, sMessage, sIcon = 0) {
        Swal.fire({
            icon: sIcon == 0 ? 'info' : sIcon,
            title: sTitle,
            text: sMessage
        })
    }

    /**
     * Muestra un mensaje con fondo de animación con confetti
     * 
     * @param {String} sTitle
     * @param {String} sMessage
     * @param {String} sIcon
     */
    static showSuccess(sTitle, sMessage, sIcon, image) {
        let base_url = window.location.origin
        let url = location.pathname.split('/');
        let img = image;
        Swal.fire({
            icon: sIcon == 0 ? 'info' : sIcon,
            title: sTitle,
            text: sMessage,
            width: 600,
            padding: '3em',
            backdrop: `
              rgba(0,0,123,0.4)
              url(` + img + `)
              left top
              no-repeat
            `
        })
    }

    static showConfirm(title, sMessage, icon) {
        return new Promise(function(resolve) {
            swal.fire({
                title: title,
                text: sMessage,
                icon: icon,
                showCancelButton: true,
                confirmButtonText: 'confirmar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                resolve(result.isConfirmed);
            });
        });
    }

    static sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}