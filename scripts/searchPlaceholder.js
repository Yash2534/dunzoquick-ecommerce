const searchInput = document.getElementById('searchInput');
const placeholders = [
  'Need milk in a flash? Just type it here!',
  'Craving fresh bread? We’ve got you covered 🍞',
  'Search trusted medicines & wellness items 💊',
  'Add some fruits to your basket 🍎🍌',
  'Cool off with a scoop of ice cream 🍦',
  'Grocery run? Delivered in minutes 🛒',
  'Your local pharmacy, now online 🚑',
  'Snacks that hit the spot—search and grab 🍪',
  'Beat the heat with cold drinks & juices 🧃',
  'Need a gadget fast? Find electronics here 🔌',
  'Pet food, toys & care delivered quick 🐶',
  'Everything your furry friend needs 🐾',
  'Fresh from the bakery to your door 🥐',
  'Craving cookies, cake, or bread? Start here 🍰',
  'Bakery favorites available near you!',
  'Personal care sorted—just type what you need 🧼',
  'Shampoo? Toothpaste? Soap? Find it instantly 💧',
  'Self-care essentials at your fingertips 💅',
  'Order your daily needs in one quick search',
  'Fresh deals on grocery, snacks & more!'
];
let index = 0;

setInterval(() => {
  searchInput.placeholder = placeholders[index];
  index = (index + 1) % placeholders.length;
}, 2000); // Changes every 3 seconds
