/**
 * MenuModel
 * Owns the restaurant's menu data and category/query filtering.
 */
window.MenuModel = {
  items: [
    {id:1,name:'Truffle Cream Pasta',category:'Pasta',price:289,color:'#e9d5b5',desc:'Silky cream sauce, mushrooms, parmesan, and truffle oil.',badge:'BESTSELLER'},
    {id:2,name:'Crispy Chicken Bowl',category:'Bowls',price:249,color:'#e4c69e',desc:'Golden chicken, garlic rice, fresh greens, and house sauce.',badge:'POPULAR'},
    {id:3,name:'Garden Pesto Pasta',category:'Pasta',price:259,color:'#bfceb0',desc:'Basil pesto, cherry tomatoes, greens, and toasted seeds.'},
    {id:4,name:'Smoky Beef Burger',category:'Burgers',price:279,color:'#dfb792',desc:'Smashed beef, cheddar, caramelized onions, and smoky mayo.'},
    {id:5,name:'Honey Garlic Wings',category:'Sides',price:229,color:'#dba980',desc:'Crispy wings glazed with sweet garlic and sesame.',badge:'NEW'},
    {id:6,name:'Mango Cloud Shake',category:'Drinks',price:149,color:'#f4ce83',desc:'Fresh mango, creamy milk, and a soft whipped finish.'},
    {id:7,name:'Classic Tiramisu',category:'Desserts',price:179,color:'#caa889',desc:'Espresso-soaked layers with mascarpone and cocoa.'},
    {id:8,name:'Citrus Garden Fizz',category:'Drinks',price:129,color:'#c8d8a8',desc:'Calamansi, lemon, sparkling water, and fresh mint.'},
    {id:9,name:'Four Cheese Pizza',category:'Pizza',price:329,color:'#edc989',desc:'Mozzarella, cheddar, parmesan, and creamy blue cheese.',badge:'POPULAR'},
    {id:10,name:'Teriyaki Salmon Bowl',category:'Bowls',price:349,color:'#d6bd9b',desc:'Glazed salmon, steamed rice, edamame, and sesame.'},
    {id:11,name:'Spicy Chicken Burger',category:'Burgers',price:269,color:'#d9a074',desc:'Crispy chicken, chili glaze, slaw, and cooling ranch.'},
    {id:12,name:'Roasted Tomato Pasta',category:'Pasta',price:239,color:'#dca27d',desc:'Slow-roasted tomato sauce, garlic, basil, and parmesan.'},
    {id:13,name:'Loaded Potato Wedges',category:'Sides',price:189,color:'#eccd8f',desc:'Crisp potato wedges, cheese sauce, herbs, and bacon.'},
    {id:14,name:'Matcha Cream Latte',category:'Drinks',price:159,color:'#b9c99d',desc:'Ceremonial matcha, fresh milk, and vanilla cream.'},
    {id:15,name:'Chocolate Lava Cake',category:'Desserts',price:199,color:'#b9957d',desc:'Warm chocolate cake with a rich molten center.',badge:'NEW'},
    {id:16,name:'Margherita Pizza',category:'Pizza',price:289,color:'#dfb47d',desc:'Tomatoes, fresh mozzarella, basil, and olive oil.'},
    {id:17,name:'Korean Beef Bowl',category:'Bowls',price:279,color:'#c69c78',desc:'Savory beef, kimchi, steamed rice, and a fried egg.'},
    {id:18,name:'Caesar Garden Salad',category:'Salads',price:219,color:'#b8c995',desc:'Crisp romaine, parmesan, croutons, and Caesar dressing.'},
    {id:19,name:'Strawberry Cheesecake',category:'Desserts',price:189,color:'#e6b5b2',desc:'Creamy cheesecake with a bright strawberry topping.'},
    {id:20,name:'Cold Brew Caramel',category:'Drinks',price:149,color:'#c3a486',desc:'Slow-steeped coffee, caramel, milk, and soft cream.'}
  ],

  categories() {
    return ['All', 'New', 'Popular', 'Bestseller', ...new Set(this.items.map(item => item.category))];
  },

  find(id) {
    return this.items.find(item => item.id === Number(id));
  },

  filter(category = 'All', query = '') {
    const badgeFilters = {New: 'NEW', Popular: 'POPULAR', Bestseller: 'BESTSELLER'};
    return this.items.filter(item =>
      (category === 'All' || (badgeFilters[category] ? item.badge === badgeFilters[category] : item.category === category)) &&
      `${item.name} ${item.desc}`.toLowerCase().includes(query.toLowerCase())
    );
  }
};
