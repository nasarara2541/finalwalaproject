
function showToast(message, type = 'success') {
    const styles = {
        success: { bg: 'bg-green-50',  border: 'border-green-200', text: 'text-green-700', icon: 'fa-circle-check',         iconColor: 'text-green-500'  },
        error:   { bg: 'bg-red-50',    border: 'border-red-200',   text: 'text-red-700',   icon: 'fa-circle-xmark',         iconColor: 'text-red-500'    },
        warning: { bg: 'bg-amber-50',  border: 'border-amber-200', text: 'text-amber-700', icon: 'fa-triangle-exclamation', iconColor: 'text-amber-500'  },
        info:    { bg: 'bg-blue-50',   border: 'border-blue-200',  text: 'text-blue-700',  icon: 'fa-circle-info',          iconColor: 'text-blue-500'   },
    };

    const s = styles[type] || styles.info;
    const toast = document.createElement('div');
    toast.className = `fixed bottom-6 right-6 z-[9999] flex items-center gap-3 border ${s.bg} ${s.border} ${s.text}
                       text-[14px] font-medium px-3 py-3 rounded-lg shadow-md transition-all duration-300 opacity-0 translate-y-2`;

    toast.innerHTML = `
        <i class="fa-solid ${s.icon} text-base ${s.iconColor}"></i>
        <span class="flex-1">${message}</span>
        <button onclick="this.closest('div').remove()"
                class="ml-2 opacity-50 hover:opacity-100 transition-opacity ${s.text}">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
    `;

    document.body.appendChild(toast);
    requestAnimationFrame(() => {
        toast.classList.remove('opacity-0', 'translate-y-2');
    });

    const timer = setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, 3000);

    toast.querySelector('button').addEventListener('click', () => {
        clearTimeout(timer);
        toast.remove();
    });
}