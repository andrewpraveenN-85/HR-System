
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="{{ asset('assetes/css/root.css') }}">
        <link rel="icon" href="{{ asset('browser_icon.png') }}" type="image/x-icon">
        <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
      <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
      <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
      <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.5.0/remixicon.css" integrity="sha512-6p+GTq7fjTHD/sdFPWHaFoALKeWOU9f9MPBoPnvJEWBkGS4PKVVbCpMps6IXnTiXghFbxlgDE8QRHc3MU91lJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
      
          <title>@yield('title', 'App')</title>
          @vite(['resources/js/app.js'])
       


    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('assetes/css/root.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <script src="https://unpkg.com/alpinejs" defer></script>
  
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
  
  
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
  
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.5.0/remixicon.css" integrity="sha512-6p+GTq7fjTHD/sdFPWHaFoALKeWOU9f9MPBoPnvJEWBkGS4PKVVbCpMps6IXnTiXghFbxlgDE8QRHc3MU91lJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    </head><section class="w-full h-screen">
    
        <div class="w-full p-8 bg-white nunito-">
          <div class="w-full flex h-[100px] space-x-8">
          <div class="w-1/2 flex flex-col h-[100px]">
              <div class="w-full h-[80px] bg-[#184E7780] rounded-[20px] rounded-tr-[60px]">
                <div class="w-full h-full flex justify-between items-center text-3xl px-24">
                <a href="#" class="text-black font-medium hover:underline">About Us</a>
              <a href="#" class="text-black font-medium hover:underline">Features</a>
              <a href="#" class="text-black font-medium hover:underline">Reviews</a>
              <a href="#" class="text-black font-medium hover:underline">FAQ</a>
                </div>
              
              </div>
          </div>
          <div class="w-1/2 h-[100px] bg-[#184E77] rounded-tr-[40px] rounded-tl-[100px]">
          </div>
          </div>
      
          <div class="w-full flex h-[600px] bg-[#184E77] rounded-b-[30px] rounded-l-[40px]">
          <div class="md:w-1/2 w-full flex flex-col space-y-8 nunito- justify-center items-start md:pl-32 pl-8">
              <p class="md:text-4xl text-2xl font-light text-white">From tiny steps to big leaps, we’ve got you covered!</p>
              <div class="w-full flex flex-col md:text-8xl text-5xl font-bold text-white leading-tight">
              <p>Simplify every HR</p>
                  <p>task with a platform </p>
                  <p>so friendly.</p>
              </div>
              <p class="md:text-5xl text-xl font-bold text-white">You’ll love every moment of managing your team.</p>
              <!-- Call to Action -->
              <div class="w-full">
                <a href="{{ route('register.form') }}">
                  <button class="bg-white text-2xl text-[#184E77] font-semibold px-8 py-3 rounded-full shadow-md hover:bg-blue-100 transition duration-200">
                      Create an account
                  </button></a>
              </div>
              
          </div>
          <!-- Image Section -->
          <div class="relative w-1/2 flex justify-center items-center md:inline hidden">
          <div class="relative w-[500px] h-[500px]">
          <!-- Top Circle -->
          <div class="absolute top-10 left-10 w-96 h-96 bg-cover bg-center rounded-full shadow-lg"
               style="background-image: url(/bg5.png)">
          </div>
      
          <!-- Right Circle -->
          <div class="absolute top-20 right-0 w-96 h-96 bg-cover bg-center rounded-full shadow-lg"
                style="background-image: url(/bg3.png)">
          </div>
      
          <!-- Bottom Circle -->
          <div class="absolute bottom-0 left-40 w-96 h-96 bg-cover bg-center rounded-full shadow-lg"
                style="background-image: url(/bg8.png)">
          </div>
        </div>
      
      
          </div>
          </div>
        </div>
          <div class="w-full md:h-[400px] h-[200px] flex flex-col justify-start items-center relative nunito-">
              <!-- Background Image -->
          <div class="absolute inset-0 z-0 opacity-80">
              <img src="/bg5.png" alt="Background" class="w-full h-full object-cover">
          </div>
          <!-- Main Content -->
          <div class="relative z-20 w-full h-full flex flex-col space-y-8 nunito- justify-center items-center md:px-64 px-12">
              <p class="md:text-5xl text-xl text-white text-center leading-normal">Our HR Management System (HRMS) is a simple yet powerful solution designed to streamline HR operations for businesses of all sizes. It offers an all-in-one platform for managing employee lifecycle, tracking attendance and leave, automating payroll, simplifying recruitment, and ensuring compliance with regulations.</p>
          </div>
          </div>
          
      <div class="w-full bg-white">
          <div class="w-full py-8 flex justify-center items-center nunito- bg-white">
              <p class="text-black md:text-6xl text-4xl font-bold">Frequently asked questions</p>
          </div>
          <div class="w-full py-8 flex flex-col jusify-center items-center md:px-32 px-8 nunito-">
          
        <!-- Question Sections -->
        <div class="mb-4 md:w-2/3 w-full" id="faq1">
          <button
            type="button"
            class="toggle-button w-full text-left flex justify-between items-center bg-[#184E7726] text-2xl font-semibold text-gray-800 px-4 py-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <span>Is this HR Management System free to use ?</span>
            <i class="ri-add-line toggle-icon h-5 w-5 text-gray-600 transition-transform duration-200"></i>
          </button>
          <div
            class="answer-section pt-4 bg-[#184E7726] hidden px-4 text-black nunito- text-xl"
          >
            <p class="mb-4">If you’re ready to manage your global team using Remote, you can sign up for a free account — no credit card is required.</p>
            <p class="mb-4">After answering some simple questions and verifying your email address, you’ll get access to the Remote platform where you can start adding, inviting, and managing your employees one at a time or in bulk.</p>
            <p class="mb-4">If you already have a Remote account, you can log in and use HR Management at any time.</p>
            <p class="mb-4">There is no limit to how many employees you can add to Remote.</p>
            <p class="mb-4">Learn about the benefits of using a global HRIS</p>
            <p class="mb-4">Take a self-guided tour of Remote HR Management</p>
          </div>
        </div>
      
        <div class="mb-4 md:w-2/3 w-full" id="faq2">
          <button
            type="button"
            class="toggle-button w-full text-left flex justify-between items-center bg-[#184E7726] text-2xl font-semibold text-gray-800 px-4 py-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <span>How can I start using this HR Management ?</span>
            <i class="ri-add-line toggle-icon h-5 w-5 text-gray-600 transition-transform duration-200"></i>
          </button>
          <div
            class="answer-section pt-4 bg-[#184E7726] hidden px-4 text-black nunito- text-xl"
          >
            <p class="mb-4">If you’re ready to manage your global team using Remote, you can sign up for a free account — no credit card is required.</p>
            <p class="mb-4">After answering some simple questions and verifying your email address, you’ll get access to the Remote platform where you can start adding, inviting, and managing your employees one at a time or in bulk.</p>
            <p class="mb-4">If you already have a Remote account, you can log in and use HR Management at any time.</p>
            <p class="mb-4">There is no limit to how many employees you can add to Remote.</p>
            <p class="mb-4">Learn about the benefits of using a global HRIS</p>
            <p class="mb-4">Take a self-guided tour of Remote HR Management</p>
          </div>
        </div>
        <div class="mb-4 md:w-2/3 w-full" id="faq3">
          <button
            type="button"
            class="toggle-button w-full text-left flex justify-between items-center bg-[#184E7726] text-2xl font-semibold text-gray-800 px-4 py-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <span>Is there a limit to how many employees I can add to HR Platform?</span>
            <i class="ri-add-line toggle-icon h-5 w-5 text-gray-600 transition-transform duration-200"></i>
          </button>
          <div
            class="answer-section pt-4 bg-[#184E7726] hidden px-4 text-black nunito- text-xl"
          >
            <p class="mb-4">If you’re ready to manage your global team using Remote, you can sign up for a free account — no credit card is required.</p>
            <p class="mb-4">After answering some simple questions and verifying your email address, you’ll get access to the Remote platform where you can start adding, inviting, and managing your employees one at a time or in bulk.</p>
            <p class="mb-4">If you already have a Remote account, you can log in and use HR Management at any time.</p>
            <p class="mb-4">There is no limit to how many employees you can add to Remote.</p>
            <p class="mb-4">Learn about the benefits of using a global HRIS</p>
            <p class="mb-4">Take a self-guided tour of Remote HR Management</p>
          </div>
        </div>
        <div class="mb-4 md:w-2/3 w-full" id="faq4">
          <button
            type="button"
            class="toggle-button w-full text-left flex justify-between items-center bg-[#184E7726] text-2xl font-semibold text-gray-800 px-4 py-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <span>What are the main features of the HR management ?</span>
            <i class="ri-add-line toggle-icon h-5 w-5 text-gray-600 transition-transform duration-200"></i>
          </button>
          <div
            class="answer-section pt-4 bg-[#184E7726] hidden px-4 text-black nunito- text-xl"
          >
            <p class="mb-4">If you’re ready to manage your global team using Remote, you can sign up for a free account — no credit card is required.</p>
            <p class="mb-4">After answering some simple questions and verifying your email address, you’ll get access to the Remote platform where you can start adding, inviting, and managing your employees one at a time or in bulk.</p>
            <p class="mb-4">If you already have a Remote account, you can log in and use HR Management at any time.</p>
            <p class="mb-4">There is no limit to how many employees you can add to Remote.</p>
            <p class="mb-4">Learn about the benefits of using a global HRIS</p>
            <p class="mb-4">Take a self-guided tour of Remote HR Management</p>
          </div>
        </div>
      </div>
      </div>
      <div class="w-full flex  justify-between items-center nunito- bg-[#F2FBFC] md:px-36 px-8">
      <div class="w-1/2 h-[200px] flex flex-col justify-center items-start nunito- space-y-8">
              <p class="text-black md:text-6xl text-3xl">Contact us</p>
              <p>Lorem ipsum dolor sit amet consectetur. Massa diam elementum urna hac sed duis.</p>
          </div>
          <div class="w-1/2 flex md:space-x-4 justify-end items-center">
        <!-- Email Button -->
        <button class="flex items-center space-x-2 md:px-6 md:py-3 md:p-0 p-2 bg-blue-900 text-white rounded-full shadow-md hover:bg-blue-800 transition">
          <p><i class="ri-mail-fill"></i></p>
          <span>Send Us Email</span>
        </button>
      
        <!-- Phone Section -->
        <div class="flex items-center space-x-3 md:px-6 md:py-3 md:p-0 p-2 bg-white text-blue-900 rounded-full shadow-md">
          <div class="flex items-center justify-center w-8 h-8 bg-blue-900 text-white rounded-full">
            <p><i class="ri-phone-line"></i></p>
          </div>
          <span>941 323 8033</span>
        </div>
      </div>
      
          </div>
      <style>
              .custom-tab {
                  clip-path: polygon(0 0, 85% 0, 75% 100%, 0% 100%);
              }
          </style>
      <section class="flex flex-col justify-center items-center space-y-16 w-full py-16 bg-gradient-to-b from-[#184E77] to-[#52B69A]">
              <div class="flex flex-col w-full justify-center items-center space-y-8 nunito-">
                  <p class="md:text-6xl text-3xl text-center animate-right-slide text-white font-bold">Reviews for our</p> 
                  <p class="md:text-6xl text-3xl text-center animate-right-slide text-white font-bold">Human Resources Management System</p> 
              </div>
              <div id="testimonialContainer" class=" grid md:grid-cols-3 grid-cols-1 gap-8 md:w-5/6 w-full place-items-center md:px-0 px-12">
                  <!-- Cards will be dynamically injected here -->
              </div>
          </section>
          <script>
          const testimonials = [
            {
          name: "Nethmi Perera",
          role: "Graphic Designer",
          image: "https://example.com/nethmi.jpg",
          rating: 5,
          review: "JAAN Vision is a game-changer! As a Sri Lankan designer, finding quick inspiration is crucial. This platform helps me generate stunning images instantly. It's perfect for brainstorming new concepts. Highly recommend it!"
      },
      {
          name: "Kasun Fernando",
          role: "Photographer",
          image: "https://static.vecteezy.com/system/resources/previews/020/911/734/non_2x/profile-avatar-user-icon-male-icon-face-icon-profile-icon-free-png.png",
          rating: 5,
          review: "This AI image generator is a fantastic tool for photographers like me. It helps me visualize new ideas and themes for my shoots in seconds. I’ve started using it to create mood boards for my work. JAAN Vision is impressive!"
      },
      {
          name: "Dilini Jayawardena",
          role: "Artist",
          image: "https://example.com/dilini.jpg",
          rating: 5,
          review: "JAAN Vision has quickly become an essential tool in my creative process. I love how easily it generates high-quality visuals. It's ideal for both professional projects and personal use."
      },
      {
          name: "Ravindu Silva",
          role: "Animator",
          image: "https://example.com/ravindu.jpg",
          rating: 5,
          review: "This platform has changed the way I approach animation! JAAN Vision helps me create reference images and backgrounds, making my workflow faster and more efficient. It's been a huge help in my projects."
      },
      {
          name: "Tharushi Gamage",
          role: "Illustrator",
          image: "https://example.com/tharushi.jpg",
          rating: 5,
          review: "As an illustrator, JAAN Vision has been amazing for idea generation. The images it creates are always sharp, and it really helps me explore new creative directions. I can’t recommend it enough!"
      },
      {
          name: "Sanjaya Wickramasinghe",
          role: "Creative Director",
          image: "https://example.com/sanjaya.jpg",
          rating: 5,
          review: "JAAN Vision has made my work as a creative director so much easier. I can now generate visual concepts much faster, saving hours of brainstorming. A must-have tool for anyone in the creative industry in Sri Lanka!"
      }
      ,
      {
          name: "Sanjaya Wickramasinghe",
          role: "Creative Director",
          image: "https://example.com/sanjaya.jpg",
          rating: 5,
          review: "JAAN Vision has made my work as a creative director so much easier. I can now generate visual concepts much faster, saving hours of brainstorming. A must-have tool for anyone in the creative industry in Sri Lanka!"
      }
      ,
      {
          name: "Sanjaya Wickramasinghe",
          role: "Creative Director",
          image: "https://example.com/sanjaya.jpg",
          rating: 5,
          review: "JAAN Vision has made my work as a creative director so much easier. I can now generate visual concepts much faster, saving hours of brainstorming. A must-have tool for anyone in the creative industry in Sri Lanka!"
      }
      ,
      {
          name: "Sanjaya Wickramasinghe",
          role: "Creative Director",
          image: "https://example.com/sanjaya.jpg",
          rating: 5,
          review: "JAAN Vision has made my work as a creative director so much easier. I can now generate visual concepts much faster, saving hours of brainstorming. A must-have tool for anyone in the creative industry in Sri Lanka!"
      }
      ,
      ];
      
        const testimonialContainer = document.getElementById("testimonialContainer");
      
        testimonials.forEach((testimonial) => {
          const ratingStars = Array(testimonial.rating).fill('<i class="ri-star-s-fill text-[#FFC700] text-3xl"></i>').join('');
      
          const card = `
            <div class="h-[230px] md:w-[400px] w-full rounded-3xl p-4 shadow-3xl bg-[#184E7799] cursor-pointer hover:scale-110 transition-transform duration-300 ease-in-out animate-left-slide-new-wel animate-left-slide-new">
              <div class="flex justify-between h-1/3 w-full">
                <div class="w-3/5 flex justify-around items-center space-x-2">
                  <p><i class="ri-user-3-line text-5xl text-white"></i></p>
                  <div class="flex flex-col justify-center items-start h-full w-[180px] font-normal">
                    <p class="text-2xl text-white">${testimonial.name}</p>
                    <p class="text-xl text-black">${testimonial.role}</p>
                  </div>
                </div>
                <div class="w-2/5 flex items-center justify-around">
                  ${ratingStars}
                </div>
              </div>
              <div class="flex items-center font-normal justify-center h-2/3 text-xl text-white tracking-wide">
                <p>${testimonial.review}</p>
              </div>
            </div>
          `;
      
          testimonialContainer.innerHTML += card;
        });
      </script>
      
      <script>
        // JavaScript to handle toggling
        document.querySelectorAll('.toggle-button').forEach((button) => {
          button.addEventListener('click', () => {
            // Get the parent div
            const parent = button.closest('.mb-4');
      
            // Toggle the hidden class on the answer section
            const answerSection = parent.querySelector('.answer-section');
            answerSection.classList.toggle('hidden');
      
            // Change background color for active question
            const isActive = !answerSection.classList.contains('hidden');
            parent.querySelector('.toggle-button').style.backgroundColor = isActive ? '#184E77' : '#184E7726';
            parent.querySelector('.toggle-button').style.color = isActive ? '#FFFFFF' : '#000000';
      
      
            // Toggle icon
            const icon = button.querySelector('.toggle-icon');
            if (isActive) {
              icon.classList.replace('ri-add-line', 'ri-subtract-line');
              icon.style.color = '#FFFFFF';
            } else {
              icon.classList.replace('ri-subtract-line', 'ri-add-line');
              icon.style.color = '#000000';
            }
          });
        });
      </script>
      
      </section>
      
      
</html>
