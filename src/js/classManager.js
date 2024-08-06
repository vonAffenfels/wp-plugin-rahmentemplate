document.addEventListener("DOMContentLoaded", function() {
    let elementorKit = document.querySelectorAll("[class*=elementor-kit]");
    let elementorInner = document.querySelectorAll(".elementor");
    let elementorKitClass = '';

    elementorKit.forEach(element => {
        element.classList.forEach(className => {
            if (className.includes('elementor-kit')) {
                element.classList.remove(className);
                elementorKitClass = className;
            }
        });
    });

    elementorInner.forEach(element => {
        if (elementorKitClass) {
            element.classList.add(elementorKitClass);
        }
    });
});
