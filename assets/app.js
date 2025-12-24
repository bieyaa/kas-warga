document.addEventListener('DOMContentLoaded', ()=>{
  const burger = document.querySelector('.burger');
  const menu = document.querySelector('.menu');
  if(burger && menu){
    burger.addEventListener('click', ()=>{
      menu.style.display = (menu.style.display==='flex' ? 'none' : 'flex');
      menu.style.flexDirection = 'column';
      menu.style.background = '#233758';
      menu.style.padding = '10px';
      menu.style.borderRadius = '10px';
    });
  }
});
