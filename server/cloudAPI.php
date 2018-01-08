<?php

namespace MicrosoftAzure\Storage\Samples;
require_once "../learningAnalytics/html/vendor/autoload.php";
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;
use MicrosoftAzure\Storage\Blob\Models\ListContainersResult;
use MicrosoftAzure\Storage\Common\ServicesBuilder;
use MicrosoftAzure\Storage\Common\ServiceException;
use MicrosoftAzure\Storage\Common\Internal\InvalidArgumentTypeException;

$connectionString = 'DefaultEndpointsProtocol=https;AccountName=learninganalytics;AccountKey=Qzu1pRubSrBoZLVzPjX/4vdlLJ5S6h/yOuRteBTGwjrRTMVFrBQTKIxpki4bsBl/+qog7IgN91rLPJjuT2AphA==';

$blobClient = ServicesBuilder::getInstance()->createBlobService($connectionString);


function createContainerSample($blobClient, $container_name)
{
    // OPTIONAL: Set public access policy and metadata.
    // Create container options object.
    $createContainerOptions = new CreateContainerOptions();
    // Set public access policy. Possible values are
    // PublicAccessType::CONTAINER_AND_BLOBS and PublicAccessType::BLOBS_ONLY.
    // CONTAINER_AND_BLOBS: full public read access for container and blob data.
    // BLOBS_ONLY: public read access for blobs. Container data not available.
    // If this value is not specified, container data is private to the account owner.
    $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);
    // Set container metadata
    $createContainerOptions->addMetaData("key1", "value1");
    $createContainerOptions->addMetaData("key2", "value2");
    try {
        // Create container.
        $blobClient->createContainer($container_name, $createContainerOptions); // give container name that is passed by user
        print("Container create successfully");
    
    } catch (ServiceException $e) {
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message.PHP_EOL;
    }
}

function uploadBlobSample($blobClient, $container_name, $file_name)
{
    $content = fopen($file_name,"r");
    $blob_name = $file_name;
    try {
        //Upload blob
        $blobClient->createBlockBlob($container_name, $blob_name, $content);    // get container name, blob name and content
    } catch (ServiceException $e) {
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message.PHP_EOL;
    }
}

function downloadBlobSample($blobClient, $container_name, $file_name)
{
    try {
        $getBlobResult = $blobClient->getBlob($container_name, $file_name);
    } catch (ServiceException $e) {
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message.PHP_EOL;
    }
    
    file_put_contents($file_name, $getBlobResult->getContentStream());
}

function listBlobsSample($blobClient, $container_name)
{
    try {
        // List blobs.
        $blob_list = $blobClient->listBlobs($container_name);
        $blobs = $blob_list->getBlobs();
    
        foreach ($blobs as $blob) {
            echo $blob->getName().": ".$blob->getUrl().PHP_EOL;
        }
    } catch (ServiceException $e) {
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message.PHP_EOL;
    }
}


// To create a container call createContainer.
//createContainerSample($blobClient, "mycontainer");


// To upload a file as a blob, use the BlobRestProxy->createBlockBlob method. This operation will
// create the blob if it doesn't exist, or overwrite it if it does. The code example below assumes 
// that the container has already been created and uses fopen to open the file as a stream.

//uploadBlobSample($blobClient, "mycontainer", "zunair 2.1.jpg");

// To download blob into a file, use the BlobRestProxy->getBlob method. The example below assumes
// the blob to download has been already created.

//downloadBlobSample($blobClient, "mycontainer", "zunair 2.1.jpg");

// To list the blobs in a container, use the BlobRestProxy->listBlobs method with a foreach loop to loop
// through the result. The following code outputs the name and URI of each blob in a container.

listBlobsSample($blobClient, "mycontainer");











//Or to leverage the asynchronous methods provided, the operation can be done in
//a promise pipeline.


/*
$containerName = '';
try {
    $containerName = basicStorageBlobOperationAsync($blobClient)->wait();
} catch (ServiceException $e) {
    $code = $e->getCode();
    $error_message = $e->getMessage();
    echo $code.": ".$error_message.PHP_EOL;
} catch (InvalidArgumentTypeException $e) {
    echo $e->getMessage().PHP_EOL;
}

*/


//delete the containers created. Uncomment the following code for the task.
/*
try {
    cleanUp($blobClient, $containerName)->wait();
} catch (ServiceException $e) {
    $code = $e->getCode();
    $error_message = $e->getMessage();
    echo $code.": ".$error_message.PHP_EOL;
}
*/





/*


function basicStorageBlobOperationAsync($blobClient)
{
    // Create the options for creating containers.
    $createContainerOptions = new CreateContainerOptions();
    // Set public access policy. Possible values are
    // PublicAccessType::CONTAINER_AND_BLOBS and PublicAccessType::BLOBS_ONLY.
    // CONTAINER_AND_BLOBS: full public read access for container and blob data.
    // BLOBS_ONLY: public read access for blobs. Container data not available.
    // If this value is not specified, container data is private to the account owner.
    $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);
    // Set container metadata
    $createContainerOptions->addMetaData("key1", "value1");
    $createContainerOptions->addMetaData("key2", "value2");
    // Construct the container name
    $containerName = "dbcontainer2" . sprintf('-%04x', mt_rand(0, 65535));
    return $blobClient->createContainer(
        $containerName,
        $createContainerOptions
    )->then(
        function ($response) use ($blobClient, $containerName) {
            // Successfully created the container, now upload a blob to the
            // container.
            echo "Container named {$containerName} created.\n";
            $content = fopen("myfile.txt", "r");
            $blob_name = "myblob";
            return $blobClient->createBlockBlobAsync(
                $containerName,
                $blob_name,
                $content
            );
        },
        null
    )->then(
        function ($putBlobResult) use ($blobClient, $containerName) {
            // Successfully created the blob, then download the blob.
            echo "Blob successfully created.\n";
            return $blobClient->saveBlobToFileAsync(
                "output.txt",
                $containerName,
                "myblob"
            );
        },
        null
    )->then(
        function ($getBlobResult) use ($blobClient, $containerName) {
            // Successfully saved the blob, now list the blobs.
            echo "Blob successfully downloaded.\n";
            return $blobClient->listBlobsAsync($containerName);
        },
        null
    )->then(
        function ($listBlobsResult) use ($containerName) {
            // Successfully get the blobs list.
            $blobs = $listBlobsResult->getBlobs();
            foreach ($blobs as $blob) {
                echo $blob->getName().": ".$blob->getUrl().PHP_EOL;
            }
            return $containerName;
        },
        null
    );
}
function cleanUp($blobClient, $containerName)
{
    
}

*/