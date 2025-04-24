    const contact = document.querySelector("#contact-btn");

    const scrollDownToFooter = () => {
        const footer = document.querySelector("footer");
        footer.scrollIntoView({ behavior: "smooth" });
    }

    contact.addEventListener('click', scrollDownToFooter);
    mobile_nav.addEventListener('click', toggleNavbar);
