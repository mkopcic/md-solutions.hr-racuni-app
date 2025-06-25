// Global Livewire event debugging
window.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, waiting for Livewire...');
});

document.addEventListener('livewire:initialized', () => {
    console.log('Livewire initialized globally');
    
    // Global debug for all Livewire events
    Livewire.hook('message.sent', () => {
        console.log('Livewire message sent');
    });
    
    Livewire.hook('message.received', () => {
        console.log('Livewire message received');
    });
});

// Test function for dialogs
window.testDialog = function(dialogId) {
    const dialog = document.getElementById(dialogId);
    if (dialog) {
        console.log(`Opening dialog: ${dialogId}`);
        dialog.showModal();
    } else {
        console.error(`Dialog not found: ${dialogId}`);
    }
}