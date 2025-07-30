export default class Helper {
  // Static variable
  static _interval = '';
  static _user = null;

  // Getter method
  static getInterval() {
    return this._interval;
  }

  // Setter method
  static setInterval(value) {
    // this._user = user;
    this._interval = value;
    // console.log('Interval set to:', value);
    // if (user) {
    //   console.log('User is:', user.name); // or any property
    // }
  }
}
