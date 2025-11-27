const https = require('https');

// Identifiants SANDBOX
const SANDBOX_SECRET_KEY = 'sk_sandbox_NaxqWgW3dWcIa9Fg08dHPkxN';
const SANDBOX_PUBLIC_KEY = 'pk_sandbox_TcBc9d1JPwbOlDzCYf7rjJCL';

// Configuration
const API_BASE_URL = 'https://sandbox-api.fedapay.com/v1';

/**
 * Fonction pour faire des requêtes HTTPS
 */
function makeRequest(method, path, data = null) {
    return new Promise((resolve, reject) => {
        const url = new URL(path, API_BASE_URL);

        const options = {
            method: method,
            headers: {
                'Authorization': `Bearer ${SANDBOX_SECRET_KEY}`,
                'Content-Type': 'application/json'
            }
        };

        const req = https.request(url, options, (res) => {
            let body = '';

            res.on('data', (chunk) => {
                body += chunk;
            });

            res.on('end', () => {
                try {
                    const response = JSON.parse(body);
                    if (res.statusCode >= 200 && res.statusCode < 300) {
                        resolve(response);
                    } else {
                        reject({ statusCode: res.statusCode, body: response });
                    }
                } catch (e) {
                    reject({ error: 'Invalid JSON response', body });
                }
            });
        });

        req.on('error', reject);

        if (data) {
            req.write(JSON.stringify(data));
        }

        req.end();
    });
}

/**
 * Test complet de l'API FedaPay
 */
async function testFedaPayAPI() {
    console.log('='.repeat(60));
    console.log('TEST API FEDAPAY - ENVIRONNEMENT SANDBOX');
    console.log('='.repeat(60));
    console.log();

    try {
        // ÉTAPE 1: Créer une transaction
        console.log('ÉTAPE 1: Création de la transaction');
        console.log('-'.repeat(60));

        const transactionData = {
            description: 'Test Ticketing App - Achat de billet',
            amount: 5000,
            currency: {
                iso: 'XOF'
            },
            callback_url: 'https://example.com/callback',
            customer: {
                firstname: 'Jean',
                lastname: 'Dupont',
                email: 'jean.dupont@example.com',
                phone_number: {
                    number: '+22997000000',
                    country: 'bj'
                }
            },
            custom_metadata: {
                ticket_id: 'TICKET-001',
                event_name: 'Concert Test',
                seat_number: 'A12'
            }
        };

        console.log('Données de la transaction:');
        console.log(JSON.stringify(transactionData, null, 2));
        console.log();

        const transaction = await makeRequest('POST', '/transactions', transactionData);

        console.log('✓ Transaction créée avec succès!');
        console.log('ID de la transaction:', transaction.v.id);
        console.log('Statut:', transaction.v.status);
        console.log('Montant:', transaction.v.amount, transaction.v.currency.iso);
        console.log();

        // ÉTAPE 2: Générer le token de paiement
        console.log('ÉTAPE 2: Génération du token de paiement');
        console.log('-'.repeat(60));

        const tokenResponse = await makeRequest('POST', `/transactions/${transaction.v.id}/token`);

        console.log('✓ Token généré avec succès!');
        console.log('Token:', tokenResponse.v.token);
        console.log('URL de paiement:', tokenResponse.v.url);
        console.log();

        // ÉTAPE 3: Récupérer les détails de la transaction
        console.log('ÉTAPE 3: Récupération des détails de la transaction');
        console.log('-'.repeat(60));

        const transactionDetails = await makeRequest('GET', `/transactions/${transaction.v.id}`);

        console.log('✓ Détails récupérés avec succès!');
        console.log('Statut actuel:', transactionDetails.v.status);
        console.log('Description:', transactionDetails.v.description);
        console.log('Client:', transactionDetails.v.customer.firstname, transactionDetails.v.customer.lastname);
        console.log('Métadonnées personnalisées:', JSON.stringify(transactionDetails.v.custom_metadata, null, 2));
        console.log();

        // RÉSUMÉ
        console.log('='.repeat(60));
        console.log('RÉSUMÉ DU TEST');
        console.log('='.repeat(60));
        console.log();
        console.log('✓ Transaction créée:', transaction.v.id);
        console.log('✓ Token généré:', tokenResponse.v.token);
        console.log('✓ URL de paiement:', tokenResponse.v.url);
        console.log();
        console.log('INSTRUCTIONS:');
        console.log('1. Copiez l\'URL de paiement ci-dessus');
        console.log('2. Ouvrez-la dans un navigateur');
        console.log('3. Effectuez un paiement de test');
        console.log('4. Vérifiez le statut de la transaction');
        console.log();
        console.log('Pour vérifier le statut plus tard:');
        console.log(`curl -X GET "${API_BASE_URL}/transactions/${transaction.v.id}" \\`);
        console.log(`  -H "Authorization: Bearer ${SANDBOX_SECRET_KEY}"`);
        console.log();

        return {
            transactionId: transaction.v.id,
            token: tokenResponse.v.token,
            paymentUrl: tokenResponse.v.url,
            status: transactionDetails.v.status
        };

    } catch (error) {
        console.error('❌ ERREUR lors du test:');
        console.error(error);
        throw error;
    }
}

// Exécuter le test
if (require.main === module) {
    testFedaPayAPI()
        .then(result => {
            console.log('='.repeat(60));
            console.log('TEST TERMINÉ AVEC SUCCÈS');
            console.log('='.repeat(60));
            process.exit(0);
        })
        .catch(error => {
            console.error('='.repeat(60));
            console.error('TEST ÉCHOUÉ');
            console.error('='.repeat(60));
            process.exit(1);
        });
}

module.exports = { testFedaPayAPI, makeRequest };
