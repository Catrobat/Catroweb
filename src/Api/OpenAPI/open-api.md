# OpenAPI Generation

## What is OpenAPI?

OpenAPI (formerly known as Swagger) is a specification for building APIs. It allows you to define your API's structure in a machine-readable format. With OpenAPI, you can generate client libraries, server stubs, documentation, and more. The OpenAPI specification is widely used for describing RESTful APIs.

## Why We Use OpenAPI

We use OpenAPI for the following reasons:

- **Standardization:** OpenAPI provides a standardized way to describe and document APIs, making it easier to communicate the structure of the API to both developers and consumers.
- **Code Generation:** OpenAPI allows for the automatic generation of client libraries and server code based on the API specification, reducing manual work and improving consistency.
- **Documentation:** With OpenAPI, we can automatically generate API documentation, which makes it easier for other developers to interact with and understand our API.
- **Validation:** OpenAPI enables the validation of incoming requests and outgoing responses based on the defined API schema, improving the reliability and robustness of the API.

## How to Run the OpenAPI Generation

To generate the OpenAPI code (e.g., client libraries, server stubs, etc.), you need to use the OpenAPI generator. This process is handled by the `npm run generate-api` script.

### Prerequisites

- **Node.js and npm**: You need to have Node.js and npm (Node Package Manager) installed to run the generator.
- **OpenAPI Generator**: We use the OpenAPI Generator to generate API code and documentation. It's a popular tool that works with the OpenAPI specification.

### Steps to Run OpenAPI Generation

1. **Install Dependencies**

   Make sure you have all the required dependencies installed. If you haven't installed them already, run the following command to install the necessary npm packages:

   ```bash
   npm install
   composer install
   ```

2. **Run the OpenAPI Generation Command**

   To generate the API server, simply run the following command. Old files will be overwritten with the new ones

   ```bash
   npm run generate-api
   ```

### Breaking Changes in the OpenAPI Spec

- **Deprecation Process:** The OpenAPI specification should **not** be changed in a way that breaks backwards compatibility without a thorough deprecation process. Any breaking changes should be communicated and coordinated with the app teams well in advance. This ensures a smooth transition and avoids breaking existing client applications that depend on the API.

  A proper deprecation process typically involves:

  - Marking the feature or endpoint as deprecated for a period of time.
  - Updating the documentation and communicating the changes to relevant teams.
  - Providing a clear timeline for the removal of deprecated features.
