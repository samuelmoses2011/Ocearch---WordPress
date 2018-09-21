'use strict';

class HelloMessage extends React.Component {
  render() {
    return React.createElement(
      "h1",
      null,
      "Hello World ",
      this.props.name
    );
  }
}

const domContainer = document.querySelector('#app');

ReactDOM.render(React.createElement(HelloMessage, { name: "React" }), domContainer);
