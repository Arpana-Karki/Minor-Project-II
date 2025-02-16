const services = [
    { 
      name: 'Cleaning', 
      subcategories: [
        { name: 'Home cleaning', price: 'NPR 1000' },
        { name: 'Floor cleaning', price: 'NPR 500' },
        { name: 'Sofa cleaning', price: 'NPR 700' },
        { name: 'Carpet cleaning', price: 'NPR 800' },
        { name: 'Office cleaning', price: 'NPR 1500' }
      ]
    },
    { 
      name: 'Electrician', 
      subcategories: [
        { name: 'General electrical work', price: 'NPR 1200' },
        { name: 'Wiring & installation', price: 'NPR 2500' },
        { name: 'Light fixture repair', price: 'NPR 800' },
        { name: 'Circuit breaker issues', price: 'NPR 1000' },
        { name: 'Electric appliance repair', price: 'NPR 1500' }
      ]
    },
    { 
      name: 'Plumbing', 
      subcategories: [
        { name: 'Pipe leaks', price: 'NPR 900' },
        { name: 'Drain cleaning', price: 'NPR 700' },
        { name: 'Faucet repairs', price: 'NPR 500' },
        { name: 'Water heater installation', price: 'NPR 2500' },
        { name: 'Toilet repairs', price: 'NPR 800' }
      ]
    },
    { 
      name: 'Decoration', 
      subcategories: [
        { name: 'Birthday decoration', price: 'NPR 3000' },
        { name: 'Anniversary decoration', price: 'NPR 3500' },
        { name: 'Wedding decoration', price: 'NPR 5000' },
        { name: 'Event decoration', price: 'NPR 4000' },
        { name: 'Corporate decoration', price: 'NPR 6000' }
      ]
    },
    { 
      name: 'Cooking', 
      subcategories: [
        { name: 'Vegetarian meals', price: 'NPR 500' },
        { name: 'Non-vegetarian meals', price: 'NPR 700' },
        { name: 'Indian cuisine', price: 'NPR 600' },
        { name: 'Nepali cuisine', price: 'NPR 650' },
        { name: 'Special dietary meals', price: 'NPR 800' }
      ]
    },
    { 
      name: 'Salon', 
      subcategories: [
        { name: 'Haircuts', price: 'NPR 500' },
        { name: 'Manicure & Pedicure', price: 'NPR 600' },
        { name: 'Facial treatments', price: 'NPR 700' },
        { name: 'Hair styling', price: 'NPR 800' },
        { name: 'Makeup', price: 'NPR 900' }
      ]
    },
    { 
      name: 'Painter', 
      subcategories: [
        { name: 'Wall painting', price: 'NPR 1000' },
        { name: 'Decorative painting', price: 'NPR 1200' },
        { name: 'Furniture painting', price: 'NPR 1500' },
        { name: 'Mural painting', price: 'NPR 2000' },
        { name: 'House exterior painting', price: 'NPR 2500' }
      ]
    }
  ];
  
  function searchServices() {
    const query = document.getElementById('service-search').value.toLowerCase();
    const result = services.filter(service => service.name.toLowerCase().includes(query) || 
                                             service.subcategories.some(sub => sub.name.toLowerCase().includes(query)));
  
    displayServices(result);
  }
  
  function displayServices(services) {
    const serviceList = document.getElementById('service-list');
    serviceList.innerHTML = ''; // Clear previous results
  
    services.forEach(service => {
      const serviceItem = document.createElement('div');
      serviceItem.classList.add('service-item');
      serviceItem.innerHTML = `
        <h3>${service.name}</h3>
        <ul>
          ${service.subcategories.map(sub => `
            <li>
              ${sub.name} - ${sub.price}
              <button onclick="bookService('${service.name}', '${sub.name}')">Book Now</button>
            </li>
          `).join('')}
        </ul>
      `;
      serviceList.appendChild(serviceItem);
    });
  }
  
  function bookService(serviceName, subcategoryName) {
    alert(`Booking service: ${subcategoryName} under ${serviceName}`);
    // You can add a modal or booking functionality here
  }
  