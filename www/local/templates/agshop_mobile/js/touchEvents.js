$(function() {

  // Get a reference to an element.
  var square = document.querySelector('.mobile-aside-dropdown-outer');

  // Create a manager to manage the element.
  var manager = new Hammer.Manager(square);

  // Create a recognizer.
  var TripleTap = new Hammer.Tap({
    event: 'tripletap',
    taps: 3
  });

  // Add the recognizer to the manager.
  manager.add(TripleTap);

  // Subscribe to the event.
  manager.on('tripletap', function(e) {
    e.target.classList.toggle('expand');
    console.log("You're triple tapping me!");
    console.log(e);
  });
});
